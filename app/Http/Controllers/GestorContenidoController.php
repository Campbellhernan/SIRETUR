<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Documento as Documento;
use App\Comentario as Comentario;
use App\Diccionario as Diccionario;
use App\Coleccion as Coleccion;
use App\Centroide as Centroide;
use App\User as User;
use GooglePlaces;
use Auth;
use NlpTools\Clustering\KMeans;
use NlpTools\Similarity\Euclidean;
use NlpTools\Clustering\CentroidFactories\Euclidean as EuclideanCF;
use NlpTools\Documents\TrainingSet;
use NlpTools\Documents\TokensDocument;
use NlpTools\FeatureFactories\DataAsFeatures;
use Wamania\Snowball\Spanish;
use DB; 

class GestorContenidoController extends Controller
{
    public function append(Request $request)
    {
        $place_id = $request['place_id'];
        $exist = DB::table('documentos')->where('place_id','=',$place_id)->count();
        if($exist <= 0){
            $descripcion = $request['descripcion'];
            $googlePlace = GooglePlaces::placeDetails($place_id,['language'=>'es']);
            if($googlePlace['status'] == 'OK'){
                $place = $googlePlace['result'];
                $documento = new Documento;
                $documento->url = $place['url'];
                $documento->place_id = $place_id;
                $documento->direccion = $place['formatted_address'];
                $documento->nombre = $place['name'];
                $documento->description = $descripcion;
                $documento->rating = $place['rating'];
                $documento->palabras_clave = ' ';
            
                if(count($place['photos']) > 0){
                    $documento->foto_referencia = $place['photos'][0]['photo_reference'];
                }else{
                    $documento->foto_referencia = ' ';
                }
                $documento->save();
                $collection = array($documento->description,$documento->direccion);
                foreach($place['reviews'] as $review){
                    $comentario = new Comentario;
                    $comentario->place_id = $place_id;
                    $comentario->comentario = $review['text'];
                    array_push($collection,$comentario->comentario);
                    $comentario->documento_id = $documento->id;
                    $comentario->save();
                }
                $palabras = self::palabrasClave($collection);
                $arrayWord = explode(' ', $palabras);
                $stemmer = new Spanish();
                foreach($arrayWord as $word_id => $word){
                    $arrayWord[$word_id] = $stemmer->stem($word);
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
        $k = $request['k'];
        
        self::kmeans($tset,$k,$arrayID);
        
        $resul['status'] = 'OK';
        $resul['prueba'] = $dictionary;
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
        
        $tset = new TrainingSet();
        $arrayID = array();
        $id = 0;
        foreach($collection as $idDoc  => $doc){
            $terms = explode(' ', $doc);
            $docCount = count($collection);
            $vector = array();
            foreach($terms as $idTerm => $term){
                $entry = $dictionary[$term];
                $vector[$term] = ($entry['postings'][$idDoc]['tf'] * log($docCount / $entry['df'], 2));
                $coleccionEntidad = new Coleccion;
                $coleccionEntidad->documento_id = $idDoc;
                $coleccionEntidad->termino = $term;
                $coleccionEntidad->tf_idf = $vector[$term];
                $coleccionEntidad->save();
            }
            $tset->addDocument(
                $idDoc, 
                new TokensDocument(self::normalise($vector))
            );    
            $arrayID[$id] = $idDoc;
            $id++;
        }
        $resul['tset'] = $tset;
        $resul['arrayID'] = $arrayID;
        return $resul;
    }
    
    public function kmeans($tset, $k, $arrayID){
        $clust = new KMeans(
            $k, // two clusters
            new Euclidean(),
            new EuclideanCF()
        );
        list($clusters,$centroids,$distances) =  $clust->cluster($tset, new DataAsFeatures());
        
        foreach($clusters as $cluster_id=> $cluster){
            foreach($cluster as $id=>$doc_id){
                $documento = Documento::find($arrayID[$doc_id]);
                $documento->cluster = $cluster_id;
                $documento->save();
            }
        }
        Centroide::query()->truncate();
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

    $string = str_replace(
      array( "¨", "º", "-", "~",
         "#", "@", "|", "!",
         "·", "$", "%", "&", "/",
         "(", ")", "?", "'", "¡",
         "¿", "[", "^", "`", "]",
         "+", "}", "{", "¨", "´",
         ">", "< ", ";", ",", ":",
         ".", '"', "“", "”","nbsp", "°","—","_"),
      '',
      $string
    );

    $string = str_replace(
      array('A','B','C','D','E','F','G','H','I','J','K','L','M',
            'N','Ñ','O','P','Q','R','S','T','U','V','W','X','Y','Z'),
      array('a','b','c','d','e','f','g','h','i','j','k','l','m',
            'n','ñ','o','p','q','r','s','t','u','v','w','x','y','z'),
      $string
    );

    $string = " ".$string." ";

    $string = str_replace(
      array(' el ',' ap ',' esta ',' estas ',' este ',' estos ',
      ' ultima ',' ultimas ',' ultimo ',' ultimos ',' a ',
      ' anadio ',' aun ',' actualmente ',' adelante ',
      ' ademas ',' afirmo ',' agrego ',' ahi ',' ahora ',
      ' al ',' algun ',' algo ',' alguna ',' algunas ',
      ' alguno ',' algunos ',' alrededor ',' ambos ',' ante ',
      ' anterior ',' antes ',' apenas ',' aproximadamente ',
      ' aqui ',' asi ',' aseguro ',' aunque ',' ayer ',' bajo ',
      ' bien ',' buen ',' buena ',' buenas ',' bueno ',
      ' buenos ',' como ',' cada ',' casi ',' cerca ',
      ' cierto ',' cinco ',' comento ',' como ',' con ',
      ' conocer ',' considero ',' considera ',' contra ',
      ' cosas ',' creo ',' cual ',' cuales ',' cualquier ',
      ' cuando ',' cuanto ',' cuatro ',' cuenta ',' da ',
      ' dado ',' dan ',' dar ',' de ',' debe ',' deben ',
      ' debido ',' decir ',' dejo ',' del ',' demas ',
      ' dentro ',' desde ',' despues ',' dice ',' dicen ',
      ' dicho ',' dieron ',' diferente ',' diferentes ',
      ' dijeron ',' dijo ',' dio ',' donde ',' dos ',
      ' durante ',' e ',' ejemplo ',' el ',' ella ',' ellas ',
      ' ello ',' ellos ',' embargo ',' en ',' encuentra ',
      ' entonces ',' entre ',' era ',' eran ',' es ',' esa ',
      ' esas ',' ese ',' eso ',' esos ',' esta ',' estan ',
      ' esta ',' estaba ',' estaban ',' estamos ',' estar ',
      ' estara ',' estas ',' este ',' estes ', ' esto ',' estos ',
      ' estoy ',' estuvo ',' ex ',' existe ',' existen ',
      ' explico ',' expreso ',' fin ',' fue ',' fuera ',
      ' fueron ',' gran ',' grandes ',' ha ',' habia ',
      ' habian ',' haber ',' habra ',' hace ',' hacen ',
      ' hacer ',' hacerlo ',' hacia ',' haciendo ',' han ',
      ' hasta ',' hay ',' haya ',' he ',' hecho ',' hemos ',
      ' hicieron ',' hizo ',' hoy ',' hubo ',' igual ',
      ' incluso ',' indico ',' informo ',' junto ',' la ',
      ' lado ',' las ',' le ',' les ',' llego ',' lleva ',
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
      ' partir ',' pasada ',' pasado ',' pero ',' pesar ',
      ' poca ',' pocas ',' poco ',' pocos ',' podemos ',
      ' podra ',' podran ',' podria ',' podrian ',' poner ',
      ' por ',' porque ',' posible ',' posee ',' proximo ',' proximos ',
      ' primer ',' primera ',' primero ',' primeros ',
      ' principalmente ',' propia ',' propias ',' propio ',
      ' propios ',' pudo ',' pueda ',' puede ',' pueden ' ,' puedes ',
      ' pues ', ' pudiera ',' que ',' que ',' quedo ',' queremos ',
      ' quien ',' quien ',' quienes ',' quiere ',' realizo ',
      ' realizado ',' realizar ',' respecto ',' si ',' solo ',
      ' se ',' senalo ',' sea ',' sean ',' segun ',' segunda ',
      ' segundo ',' seis ',' ser ',' sera ',' seran ',' seria ',
      ' si ',' sido ',' siempre ',' siendo ',' siete ',
      ' sigue ',' siguiente ',' sin ',' sino ',' sobre ',
      ' sola ',' solamente ',' solas ',' solo ',' solos ',
      ' son ',' su ',' sus ',' tal ',' tambien ',' tampoco ',
      ' tan ',' tanto ',' tenia ',' tendra ',' tendran ',
      ' tenemos ',' tener ',' tenga ',' tengo ',' tenido ',
      ' tercera ',' tiene ',' tienen ',' toda ',' todas ',
      ' todavia ',' todo ',' todos ',' total ',' tras ',
      ' trata ',' traves ',' tres ',' tuvo ', ' ubicado ',' un ',' una ',
      ' unas ',' uno ',' unos ',' usted ',' va ',' vamos ', ' venezuela ',
      ' van ',' varias ',' varios ',' veces ',' ver ',' vez ',
      ' y ',' ya ',' yo ', '  '), ' ',$string);

     $string = str_replace('  ', ' ', $string);
      return $string;
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
        $documentos = Documento::select('place_id','nombre')->get();
        return $documentos;
    }
}
