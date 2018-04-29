<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Documento as Documento;
use App\Comentario as Comentario;
use App\Diccionario as Diccionario;
use App\Coleccion as Coleccion;
use App\Centroide as Centroide;
use App\Custom\Stemmer as Stemmer;
use App\Custom\Helper as Helper;
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
                $palabras = Helper::palabrasClave($collection);
                $arrayWord = explode(' ', $palabras);
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
                        $total = Helper::norma($coordenada_i)*Helper::norma($coordenada_j);
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
