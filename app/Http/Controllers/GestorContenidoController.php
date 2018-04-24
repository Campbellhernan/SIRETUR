<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Documento as Documento;
use App\Comentario as Comentario;
use App\Diccionario as Diccionario;
use App\Coleccion as Coleccion;
use App\Centroide as Centroide;
use App\Custom\Stemmer as Stemmer;
use App\Custom\Kmeans as Kmeans;
use App\User as User;
use GooglePlaces;
use Auth;

use DB; 

class GestorContenidoController extends Controller
{
    public function append(Request $request)
    {
        $place_id = $request['place_id'];
        $exist = DB::table('documentos')->where('place_id','=',$place_id)->count();
        if($exist <= 0){
            $descripcion = $request['descripcion'];
            $fuente = $request['fuente'];
            $googlePlace = GooglePlaces::placeDetails($place_id,['language'=>'es']);
            if($googlePlace['status'] == 'OK'){
                $place = $googlePlace['result'];
                $documento = new Documento;
                $documento->url = $place['url'];
                $documento->place_id = $place_id;
                $documento->direccion = $place['formatted_address'];
                $documento->nombre = $place['name'];
                $documento->description = $descripcion;
                $documento->fuente_descripcion = $fuente;
                $documento->latitud = $place['geometry']['location']['lat'];
                $documento->longitud = $place['geometry']['location']['lng'];
                $documento->palabras_clave = ' ';
                $documento->save();
                $collection = array($documento->description,$documento->direccion);
                if(isset($place['rating'])){
                    $documento->rating = $place['rating'];
                    foreach($place['reviews'] as $review){
                        $comentario = new Comentario;
                        $comentario->place_id = $place_id;
                        $comentario->nombre_usuario = $review['author_name'];
                        $comentario->origen = 'Google';
                        $comentario->rating = $review['rating'];
                        $comentario->fecha_publicacion = date("Y-m-d H:i:s", $review['time']);
                        $comentario->comentario = $review['text'];
                        array_push($collection,$comentario->comentario);
                        $comentario->save();
                    }
                }
                $palabras = self::palabrasClave($collection);
                $arrayWord = explode(' ', $palabras);
                //$stemmer = new Spanish();
                foreach($arrayWord as $word_id => $word){
                    $arrayWord[$word_id] =  Stemmer::stemm($word);
                }
                $palabras = implode(' ', $arrayWord);
                $documento->palabras_clave = $palabras;
                $documento->save();
            }
            $status = $googlePlace['status'];
        }else{
            $status = 'Existe';
        }
        $resul['status'] = $status;
        return $resul;
    }
    
    public function cluster(Request $request){
        $dictionary = array();
        $docCount = array();
        $collection = array();
        
        self::aggregate(false); //Actualiza los comentarios de los sitios turisticos.
        
        $documentos = Documento::all();
        
        foreach ($documentos as $documento) {
            $collection[$documento->id] = $documento->palabras_clave;
        }

        foreach($collection as $docID => $doc) {
            $terms = explode(' ', $doc);
            $docCount[$docID] = count($terms);
            foreach($terms as $term) {
                if(!isset($dictionary[$term])) {
                        $dictionary[$term] = array('df' => 0, 'postings' => array());
                }
                if(!isset($dictionary[$term]['postings'][$docID])) {
                    $dictionary[$term]['df']++;
                    $dictionary[$term]['postings'][$docID] = array('tf' => 0);
                }
                $dictionary[$term]['postings'][$docID]['tf']++;
            }
        }
        
        $object = self::saveCollection($collection,$dictionary);
        $tset = $object['tset'];
        $arrayID = $object['arrayID'];
        //$k = $request['k'];
        
        self::kmeans($tset,/*$k,*/$arrayID);
        $sim_promedio = self::similitud_promedio();
        $resul['status'] = 'OK';
        $resul['items'] = $sim_promedio['resul'];
        $resul['coordenadas'] = $sim_promedio['coordenadas'];
        return $resul;
    }
    
    public function palabrasClave($collection){
        $resul = '';
        foreach($collection as $elemento){
            $resul = trim($resul);
            $resul = $resul.self::limpiar(trim($elemento));
        }
        return trim($resul);
    }
    
    public function saveCollection($collection, $dictionary){
        Diccionario::query()->truncate();
        Coleccion::query()->truncate();
        foreach($dictionary as $indice => $termino){
            $diccionario = new Diccionario;
            $diccionario->termino = $indice;
            $diccionario->cantidad = $termino['df'];
            $diccionario->save();
        }

        $arrayID = array();
        $id = 0;
        $data = array();
        foreach($collection as $idDoc  => $doc){
            $terms = explode(' ', $doc);
            $terms = array_unique($terms);
            $docCount = count($collection);
            $vector = array();
            foreach($terms as $idTerm => $term){
                $entry = $dictionary[$term];
                $vector[$term] = ($entry['postings'][$idDoc]['tf'] * log($docCount / $entry['df'], 2));
            }
            
            $vector = self::normalise($vector);
            
            foreach($terms as $idTerm => $term){
                $coleccionEntidad = new Coleccion;
                $coleccionEntidad->documento_id = $idDoc;
                $coleccionEntidad->termino = $term;
                $coleccionEntidad->tf_idf = $vector[$term];
                $coleccionEntidad->save();
            }
            $data[$idDoc] = $vector;
            
            $arrayID[$id] = $idDoc;
            $id++;
        }
        $resul['tset'] = $data;
        $resul['arrayID'] = $arrayID;
        return $resul;
    }
    
    public function kmeans($tset, /*$k,*/ $arrayID){
        
        $dimensions = Diccionario::count();
        $centroides = DB::table('centroides')->select('centroide','termino','valor')
                                    ->get()
                                    ->groupBy('centroide')
                                    ->map(function ($item, $key) {
                                        return $item->pluck('valor','termino'); 
                                    });
                                            
        $clasificacion = Kmeans::kmeans_inicial($tset,$dimensions,$centroides);
        //$clasificacion = Kmeans::kmeans($tset,$k,$dimensions);
        $clusters = $clasificacion['mapping'];
        foreach($clusters as $cluster_id=> $cluster){
                $documento = Documento::find($cluster_id);
                $documento->cluster = $cluster;
                $documento->save();
        }
        Centroide::query()->truncate();
        $centroids = $clasificacion['centroides'];
        foreach($centroids as $centroid_id => $centroid){
            foreach ($centroid as $termino => $coordenada) {
                $centroide = new Centroide;
                $centroide->centroide = $centroid_id;
                $centroide->termino = $termino;
                $centroide->valor = $coordenada;
                $centroide->save();
            }
        }
        return $centroids;
    }
    
    public function limpiar($string) {
    $string = preg_replace('/\s+/', ' ', trim($string));
    $string = str_replace(
      array('1', '2', '3', '4', '5', '6', '7', '8', '9', '0'),
      '',
      $string
    );

       $string = str_replace(
        array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
        array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
        $string
    );
 
    $string = str_replace(
        array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
        array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
        $string
    );
 
    $string = str_replace(
        array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
        array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
        $string
    );
 
    $string = str_replace(
        array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
        array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
        $string
    );
 
    $string = str_replace(
        array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
        array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
        $string
    );
 
    $string = str_replace(
        array('ñ', 'Ñ', 'ç', 'Ç'),
        array('n', 'N', 'c', 'C',),
        $string
    );

    $string = stripcslashes($string);

    $string = preg_replace('([^A-Za-z0-9])', ' ', $string);

    $string = strtolower($string);

    $string = " ".$string." ";

    $string = str_replace(
      array(' el ',' ap ',' esta ',' estas ',' este ',' estos ',
      ' ultima ',' ultimas ',' ultimo ',' ultimos ',' a ',
      ' anadio ',' aun ',' actualmente ',' adelante ',
      ' ademas ',' afirmo ',' agrego ',' ahi ',' ahora ',
      ' al ',' algun ',' algo ', ' alli ', ' alla ',' alguna ',' algunas ',
      ' alguno ',' algunos ',' alrededor ',' ambos ',' ante ',
      ' anterior ',' antes ',' apenas ',' aproximadamente ',
      ' aqui ',' asi ', ' area ', ' mejor ', ' falta ' ,' aseguro ', ' atencion ',' aunque ',' ayer ',' bajo ',
      ' bien ',' buen ',' buena ',' buenas ',' bueno ',
      ' buenos ',' como ',' cada ',' casi ', ' carabobo ',' cerca ',
      ' cierto ',' cinco ',' comento ',' como ',' con ',
      ' conocer ',' considero ',' considera ',' contra ',
      ' cosas ',' creo ',' cual ', ' centro ', ' ciudad ',' cuales ',' cualquier ',
      ' cuando ',' cuanto ',' cuatro ',' cuenta ',' da ',
      ' dado ',' dan ',' dar ',' de ',' debe ',' deben ',
      ' debido ',' decir ',' dejo ',' del ',' demas ',
      ' dentro ',' desde ',' despues ',' dice ',' dicen ',
      ' dicho ',' dieron ',' diferente ',' diferentes ', ' dias ', ' dia ',
      ' disfrutar ', ' disfrute ',' disfrutando ', ' espacio ', ' estado ',
      ' dijeron ',' dijo ',' dio ',' donde ',' dos ', 
      ' durante ',' e ',' ejemplo ',' el ',' ella ',' ellas ',
      ' ello ',' ellos ',' embargo ',' en ',' encuentra ',
      ' entonces ',' entre ',' era ',' eran ',' es ',' esa ',
      ' esas ',' ese ',' eso ',' esos ',' esta ',' estan ',
      ' esta ',' estaba ',' estaban ',' estamos ',' estar ',
      ' estara ',' estas ',' este ',' estes ', ' esto ',' estos ',
      ' estoy ',' estuvo ',' ex ', ' excelente ',' existe ',' existen ',
      ' explico ',' expreso ',' fin ',' fue ',' fuera ',
      ' fueron ',' gran ',' grandes ',' ha ',' habia ',
      ' habian ',' haber ',' habra ',' hace ',' hacen ',
      ' hacer ',' hacerlo ',' hacia ',' haciendo ',' han ',
      ' hasta ',' hay ',' haya ',' he ',' hecho ',' hemos ',
      ' hicieron ',' hizo ',' hoy ',' hubo ',' igual ',
      ' incluso ',' indico ',' informo ', ' instalaciones ',' junto ',' la ',
      ' lado ',' las ',' le ',' les ',' llego ',' lleva ' , ' llevo ',
      ' llevar ',' lo ',' los ',' luego ',' lugar ',' mas ',
      ' manera ',' manifesto ',' mayor ',' me ',' mediante ',
      ' mejor ',' menciono ',' menos ',' mi ',' mientras ',
      ' misma ',' mismas ',' mismo ',' mismos ',' momento ',
      ' mucha ',' muchas ',' mucho ',' muchos ',' muy ',
      ' nada ',' nadie ',' ni ',' ningun ',' ninguna ',
      ' ningunas ',' ninguno ',' ningunos ',' no ',' nos ',
      ' nosotras ',' nosotros ',' nuestra ',' nuestras ',
      ' nuestro ',' nuestros ',' nueva ',' nuevas ',' nuevo ',
      ' nuevos ',' nunca ',' o ',' ocho ',' otra ',' otras ',
      ' otro ',' otros ',' pais ',' para ',' parece ',' parte ',
      ' partir ',' pasada ', ' pasar ', ' pasarla ', ' pasarlo ', 
      ' pasan ', ' pasado ',' pero ',' pesar ', ' pasara ',
      ' poca ',' pocas ',' poco ',' pocos ',' podemos ',
      ' podra ',' podran ',' podria ',' podrian ',' poner ',
      ' por ',' porque ',' posible ',' posee ',' proximo ',' proximos ',
      ' primer ',' primera ',' primero ',' primeros ',
      ' principalmente ',' propia ',' propias ',' propio ',
      ' propios ',' pudo ',' pueda ',' puede ',' pueden ' ,' puedes ',
      ' pues ', ' pudiera ',' que ',' que ',' quedo ',' queremos ',
      ' quien ',' quien ',' quienes ',' quiere ',' realizo ',
      ' realizado ',' realizar ', ' recomendado ', ' recomiendo ',' respecto ',' si ',' solo ',
      ' se ',' senalo ',' sea ',' sean ',' segun ',' segunda ',
      ' segundo ',' seis ',' ser ',' sera ',' seran ',' seria ',
      ' si ',' sido ',' siempre ',' siendo ',' siete ',' ofrece ',
      ' servicio ', ' servicios ', ' sitio ', ' sitios ',' llegar ', ' opcion ',
      ' sigue ',' siguiente ',' sin ',' sino ',' sobre ', ' etc ' ,
      ' sola ',' solamente ',' solas ',' solo ',' solos ',
      ' son ',' su ',' sus ',' tal ',' tambien ',' tampoco ',
      ' tan ',' tanto ',' tenia ',' tendra ',' tendran ',
      ' tenemos ',' tener ',' tenga ',' tengo ',' tenido ',
      ' tercera ',' tiene ',' tienen ',' toda ',' todas ', ' general ',
      ' todavia ',' todo ',' todos ',' total ',' tras ', ' ubicado ' , ' ubicacion ',
      ' trata ',' traves ',' tres ',' tuvo ', ' ubicado ',' un ',' una ',
      ' unas ',' uno ',' unos ', ' ubicacion ',' usted ',' va ',' valencia ',' vamos ', ' venezuela ', 
      ' valenciano ', ' valenciana ', ' venezolano ', ' venezolana ', ' km ',
      ' van ',' varias ',' varios ',' veces ',' ver ',' vez ', ' zona ', ' zonas ',
      ' ya ',' yo ', '  ', ' a ', ' b ', ' c ', ' d ', ' e ', ' f ', ' g ', 
      ' h ', ' i ', ' j ', ' k ', ' m ', ' n ', ' ñ ', ' o ', ' p ', ' q ', ' r ',
      ' s ', ' t ', ' u ', ' w ', ' x ', ' y ', ' z '), ' ',$string);

     $string = preg_replace('/\s+/', ' ', $string);  
      return $string;
  }
  
    public function norma($vector){
        $total = 0;
        foreach ($vector as $key => $value) {
          $total = $total + ($value*$value);
        }
        $total= sqrt($total);
        return($total);
    }
  
   public function normalise($doc) {
        $total = 0;
        foreach($doc as $entry) {
                $total += $entry*$entry;
        }
        $total = sqrt($total);
        
        foreach($doc as &$entry) {
            if($total > 0){
                $entry = $entry/$total;
            }else{
                $entry = 0;
            }
        }
        return $doc;
    }
    public function documents(){
        $documentos = Documento::select('place_id','nombre', 'latitud','longitud')->get();
        return $documentos;
    }
    
    public function content(Request $request){
        $place_id = $request['place_id'];
        $documento = Documento::where('place_id','=',$place_id)->first();
        $comentarios = Comentario::where('place_id','=',$place_id)->get();
        $recomendaciones = self::randomSite($documento->cluster,$place_id,3);
        $resul['status'] = 'OK';
        $resul['documento'] = $documento;
        $resul['recomendaciones'] = $recomendaciones;
        $resul['comentario'] = $comentarios->map(function ($value) {
                                    $value->avatarColor = self::avatarColor();
                                    return $value;
                                });
        return $resul;
    }
    
    public function publicar(Request $request)
    {
        $place_id = $request['place_id'];
        $text = $request['comentario'];
        $rating = $request['rating'];
        $comentario = new Comentario;
        $comentario->place_id = $place_id;
        $comentario->nombre_usuario = Auth::user()->name;
        $comentario->origen = 'SIRETUR';
        $comentario->rating = $rating;
        $comentario->fecha_publicacion = date("Y-m-d H:i:s");
        $comentario->comentario = $text;
        $comentario->save();
        $resul['status'] = 'OK';
        $resul['comentario'] = $comentario;
        return $resul;
    }
    
    public function aggregate($nuevo){
        $documentos = Documento::all();
        $resultado = array();
        foreach($documentos as $documento){
            if($nuevo){
                $googlePlace = GooglePlaces::placeDetails($documento->place_id,['language'=>'es']);
                $place = $googlePlace['result'];
                if(isset($place['rating'])){
                    foreach($place['reviews'] as $review){
                        $comentario = Comentario::firstOrNew(array( 'place_id' => $documento->place_id,
                                                                    'nombre_usuario' => $review['author_name'],
                                                                    'origen' => 'Google',
                                                                    'rating' => $review['rating'],
                                                                    'fecha_publicacion' =>  date("Y-m-d H:i:s", $review['time'])));
                        $comentario->comentario = $review['text'];
                        $comentario->nombre_usuario = $review['author_name'];
                        $comentario->origen = 'Google';
                        $comentario->rating = $review['rating'];
                        $comentario->fecha_publicacion =date("Y-m-d H:i:s", $review['time']);
                        $comentario->save();
                    }
                }
            }
            $comentarios = Comentario::where('place_id','=',$documento->place_id)->pluck('comentario');
            $comentarios->push($documento->description);
            $comentarios->push($documento->direccion);
            $palabras = self::palabrasClave($comentarios);
            $arrayWord = explode(' ', $palabras);

            foreach($arrayWord as $word_id => $word){
                $arrayWord[$word_id] = Stemmer::stemm($word);
            }
            $palabras = implode(' ', $arrayWord);
            $documento->palabras_clave = $palabras;
            $documento->save();
        }
    }
    
    private function avatarColor(){
        $colores = ['red','pink','purple','deep-purple','indigo','blue',
                    'light-blue','cyan','teal','green','light-green','lime',
                    'yellow','amber','orange','deep-orange','brown',
                    'blue-grey','grey'];
        return array_random($colores);
    }
    
    public function randomSite($cluster, $place_id, $num){
        return Documento::where(array(array('cluster','=' ,$cluster),array('place_id','!=' ,$place_id)))->inRandomOrder()->take($num)->get();
    }
    public function metrics(){
        $sim_promedio = self::similitud_promedio();
        $resul['status'] = 'OK';
        $resul['items'] = $sim_promedio['resul'];
        $resul['centroides'] = DB::table('centroides')->select('centroide','termino','valor')
                                    ->get()
                                    ->groupBy('centroide')
                                    ->map(function ($item, $key) {
                                        return $item->pluck('valor','termino'); 
                                    });
        return $resul;
    }
    
    public function permit(){
        $user = User::all();
        $resul['status'] = 'OK';
        $resul['items'] = $user;
        return $resul;
    }
    
    public function similitud_promedio(){
        $resul = array();
        $documentos = Documento::select('id','cluster')->get();
        $cluster = $documentos->groupBy('cluster');
        //$coordenadas = array();
        foreach($cluster as $cluster_id => $documentos_id){
            $sim_promedio = 0;
            $indices = $documentos_id->map(function ($item, $key) {
                                        return $item->id; 
                                    });
            $coordenadas = DB::table('coleccions')->whereIn('documento_id', $indices)
                                            ->select('documento_id','termino','tf_idf')
                                            ->get()
                                            ->groupBy('documento_id')
                                            ->map(function ($item, $key) {
                                                return $item->pluck('tf_idf','termino'); 
                                            });
            foreach($coordenadas as $doc_i => $coordenada_i) {
                foreach($coordenadas as $doc_j => $coordenada_j) {
                    //if($doc_i != $doc_j){
                        $dist = 0;
                        $total = self::norma($coordenada_i)*self::norma($coordenada_j);
                        foreach ($coordenada_j as $word => $peso) {
                            if($coordenada_i->has($word)){
                                $dist += $coordenada_i[$word]*$peso;
                            }
                        }
                        $dist = $dist/$total;
                        $sim_promedio = $sim_promedio + $dist;
                    //}
                }
            }
            $sim_promedio = $sim_promedio/ ($coordenadas->count() * $coordenadas->count());
            $item['similitud'] = $sim_promedio;
            $item['cluster'] = $cluster_id;
            $item['cantidad'] = $documentos_id->count();
            array_push($resul,$item);
        }
        $resultado['resul'] = $resul;
        $resultado['coordenadas'] = $coordenadas;
        return $resultado;
    }
    public function UpdatePermit(Request $request)
    {
        $email = $request['email'];
        $perfil = $request['perfil'];
        $user = User::where('email', $email)->update(['perfil'=>$perfil]);
    }
}
