<?php

namespace App\Http\Controllers;

use DB; 
use Illuminate\Http\Request;
use App\Documento as Documento;
use App\Comentario as Comentario;
use App\Diccionario as Diccionario;
use App\Coleccion as Coleccion;
use App\Centroide as Centroide;
use App\Custom\Stemmer as Stemmer;
use App\Custom\Helper as Helper;
use App\Custom\Kmeans as Kmeans;
use App\User as User;
use GooglePlaces;
use Auth;

class GestorGruposController extends Controller
{
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
        
        self::kmeans($tset/*,$k,$arrayID*/);
        $sim_promedio = self::similitud_promedio();
        $resul['status'] = 'OK';
        $resul['items'] = $sim_promedio['resul'];
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
            $palabras = Helper::palabrasClave($comentarios);
            $arrayWord = explode(' ', $palabras);

            foreach($arrayWord as $word_id => $word){
                $arrayWord[$word_id] = Stemmer::stemm($word);
            }
            $palabras = implode(' ', $arrayWord);
            $documento->palabras_clave = $palabras;
            $documento->save();
        }
    }
    
    public function saveCollection($collection, $dictionary){
        Coleccion::truncate();
        foreach($dictionary as $indice => $termino){
            $diccionario = Diccionario::updateOrCreate(['termino' => $indice] , ['cantidad'=>$termino['df']]);
        }

        $data = collect();
        foreach($collection as $idDoc  => $doc){
            $terms = explode(' ', $doc);
            $terms = array_unique($terms);
            $docCount = count($collection);
            $vector = array();
            
            foreach($terms as $term){
                $entry = $dictionary[$term];
                $vector[$term] = ($entry['postings'][$idDoc]['tf'] * log($docCount / $entry['df'], 2));
            }
            
            $vector = Helper::normalise($vector);
            $docColection = array();
            foreach($vector as $term => $value){
                array_push($docColection,array("documento_id" => $idDoc,"termino" => $term,"tf_idf"=>$value,"created_at"=>new \DateTime(),"updated_at"=>new \DateTime()));
            }
            Coleccion::insert($docColection);
            $data[$idDoc] = $vector;
        }
        $resul['tset'] = $data;
        return $resul;
    }
    
    public function kmeans($tset/*, /*$k, $arrayID*/){
        
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
            $documento = Documento::where("id",$cluster_id)->update(["cluster" => $cluster]);
        }
        
        $centroides = $clasificacion['centroides'];
        Centroide::truncate();
        foreach($centroides as $centroid_id => $centroid){
            $insertCentroid = array();
            foreach ($centroid as $termino => $coordenada) {
                array_push($insertCentroid,array("centroide" => $centroid_id,"termino" => $termino,"valor"=>$coordenada,"created_at"=>new \DateTime(),"updated_at"=>new \DateTime()));                
                //$centroide = Centroide::updateOrCreate(["centroide" => $centroid_id,"termino" => $termino] , ["valor"=>$coordenada]);
            }
            Centroide::insert($insertCentroid);            
        }
        return $centroides;
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
}
