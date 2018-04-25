<?php

namespace App\Custom;

class Helper {
    public static function limpiar($string) {
        
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
  
    public static function norma($vector){
        $total = 0;
        foreach ($vector as $key => $value) {
          $total = $total + ($value*$value);
        }
        $total= sqrt($total);
        return($total);
    }
  
    public static function normalise($doc) {
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
    
    public static function palabrasClave($collection){
        $resul = '';
        foreach($collection as $elemento){
            $resul = trim($resul);
            $resul = $resul.self::limpiar(trim($elemento));
        }
        return trim($resul);
    }
}