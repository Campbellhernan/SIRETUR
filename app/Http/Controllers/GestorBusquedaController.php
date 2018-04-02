<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Wamania\Snowball\Spanish;
use App\Documento as Documento;
use App\Comentario as Comentario;
use App\Diccionario as Diccionario;
use App\Coleccion as Coleccion;
use App\Centroide as Centroide;
use App\Caracteristica as Caracteristica;
use DB; 
use App\User as User;
use Auth;
use App\Custom\Stemmer as Stemmer;

class GestorBusquedaController extends Controller
{
    public function search(Request $request){
        
        $query = trim(self::limpiar($request['textarea']));
        $terms = explode(' ', $query);
        $stemmer = new Spanish();
        
        foreach($terms as $word_id => $word){
            $terms[$word_id] = $stemmer->stem($word);
        }
        $vector = array();
        foreach($terms as $idTerm => $term){
            $vector[$term] = 0.8;
        }
        
        $vector = self::normalise($vector);
        
        $short = 0;
        $cluster = null;
        $centroides = DB::table('centroides')->select('centroide')->distinct()->get();
        $coordenadas = array();
        
        foreach ($centroides as $centroide) {
            $coordenada = DB::table('centroides')->where('centroide', $centroide->centroide)
                                                                        ->pluck('valor','termino')
                                                                        ->map(function ($item, $key) {return floatval($item);});
            //$coordenadas[$centroide->centroide] = $coordenada;
            $dist = 0;
            $total = self::norma($vector)*self::norma($coordenada);
            foreach ($vector as $word => $peso) {
                if($coordenada->has($word)){
                    $dist += $coordenada[$word]*$peso;
                }
            }
            $dist = $dist/$total;
            $distancia[$centroide->centroide] = $dist;
            if($dist > $short){
              $short = $dist;
              $cluster = $centroide->centroide;
            }
        }
        
        $page = $request['current_page'];
        $perPage = $request['perPage'];
        
        $resul = self::searchIntoCluster($vector,$cluster,$page);

        $filtrado = $resul['filtrado'];
        
        $documentos = $filtrado->forPage($page, $perPage)->values();
        
        $terminos = DB::table('diccionarios')->whereIn('termino', $terms)->get();
        
        $terminos->each(function ($item, $key) {
            $caracteristica = Caracteristica::firstOrCreate(
                ['termino' => $item->termino,'usuario_id' => Auth::user()->id]
            );
        });
        
        $result['status'] = 'OK';
        $result['documentos'] = $documentos;
        $result['total'] = $filtrado->count();
        return $result;
    }
    
    public function searchIntoCluster($vector, $cluster, $page){
        $documentos = Documento::where('cluster','=' ,$cluster)->get();
        foreach($documentos as $documento) {
            $coordenada = DB::table('coleccions')->where('documento_id', $documento->id)
                                                 ->pluck('tf_idf','termino')
                                                 ->map(function ($item, $key) {return floatval($item);});
            $dist = 0;
            $total = self::norma($vector)*self::norma($coordenada);
            foreach ($vector as $word => $peso) {
                if($coordenada->has($word)){
                    $dist += $coordenada[$word]*$peso;
                }
            }
            $dist = $dist/$total;
            $documento->relevancia = $dist;
        }
        
        $filtrado = $documentos->filter(function ($documento) {
            return $documento->relevancia > 0;
        })->values();
        
        $resul['filtrado'] = $filtrado;
        return $resul;
    }
    
    public function norma($vector){
        $total = 0;
        foreach ($vector as $key => $value) {
          $total = $total + ($value*$value);
        }
        $total= sqrt($total);
        return($total);
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
}
