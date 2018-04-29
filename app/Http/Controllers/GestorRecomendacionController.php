<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User as User;
use Auth;
use App\Documento as Documento;
use App\Caracteristica as Caracteristica;
use DB;

class GestorRecomendacionController extends Controller
{
    public function recommendation(Request $request){
        $terminos = DB::table('diccionarios')
                        ->join('caracteristicas', 'caracteristicas.termino', '=', "diccionarios.termino")
                        ->where('caracteristicas.usuario_id', '=', Auth::user()->id)
                        ->select("diccionarios.termino","diccionarios.cantidad")->get();
                        
        $docCount = Documento::all()->count();
        $vector = [];
        
        foreach($terminos as $idTerm => $term){
            $vector[$term->termino] = (1 * log($docCount / $term->cantidad, 2));
        }
        
        if(collect($vector)->count() > 0){
            $vector = self::normalise($vector);               
                    $short = 0;
            $cluster = null;
            $centroides = DB::table('centroides')->select('centroide')->distinct()->get();
            $coordenadas = array();
            foreach ($centroides as $centroide) {
                $coordenada = DB::table('centroides')->where('centroide', $centroide->centroide)
                                                                            ->pluck('valor','termino')
                                                                            ->map(function ($item, $key) {return floatval($item);});
                $coordenadas[$centroide->centroide] = $coordenada;
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
            $documentos = Documento::where('cluster','=' ,$cluster)->paginate(10,['*'],'page',$page);
            $result['documentos'] = $documentos;
        }
        $result['status'] = 'OK';
        
        
        return $result;
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
    
    public function norma($vector){
        $total = 0;
        foreach ($vector as $key => $value) {
          $total = $total + ($value*$value);
        }
        $total= sqrt($total);
        return($total);
    }
}
