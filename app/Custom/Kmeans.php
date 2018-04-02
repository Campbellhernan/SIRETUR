<?php

namespace App\Custom;

class Kmeans {
    public static function kmeans($datos, $k, $dimensions) {
        $mapping = array();
        $centroides = Kmeans::initialiseCentroids($datos,$k,$dimensions);
        $cambio = false;
        while(!$cambio){
          $new_mapping = Kmeans::asignarCentroides($datos,$centroides);
          if($mapping === $new_mapping){
            $cambio = true;
          }
          else {
            $mapping = $new_mapping;
            $centroides = Kmeans::actualizarCentroides($mapping,$datos,$k);
          }
        }
        $resul['mapping'] = $mapping;
        $resul['centroides'] = $centroides;
        return $resul;
    }
    public static function kmeans_inicial($datos, $dimensions, $centroides) {
      $k = count($centroides);
        $mapping = array();
        //$centroides = Kmeans::initialiseCentroids($datos,$k,$dimensions);
        $cambio = false;
        while(!$cambio){
          $new_mapping = Kmeans::asignarCentroides($datos,$centroides);
          if($mapping === $new_mapping){
            $cambio = true;
          }
          else {
            $mapping = $new_mapping;
            $centroides = Kmeans::actualizarCentroides($mapping,$datos,$k);
          }
        }
        $resul['mapping'] = $mapping;
        $resul['centroides'] = $centroides;
        return $resul;
    }

   public static function initialiseCentroids(array $data, $k, $dimensions) {
    $centroids = array();
    $dimmax = array();
    $dimmin = array();
    foreach($data as $document) {
      foreach($document as $dimension => $val) {
        if(!isset($dimmax[$dimension]) || $val > $dimmax[$dimension]) {
          $dimmax[$dimension] = $val;
        }
        if(!isset($dimmin[$dimension]) || $val < $dimmin[$dimension]) {
          $dimmin[$dimension] = $val;
        }
      }
    }
    for($i = 0; $i < $k; $i++) {
      $centroids[$i] = Kmeans::initialiseCentroid($dimensions, $dimmax, $dimmin);
    }
    return $centroids;
  }

   public static function initialiseCentroid($dimensions, $dimmax, $dimmin) {
    $total = 0;
    $centroid = array();
    foreach($dimmin as $key => $value) {
            $centroid[$key] = (rand($dimmin[$key] * 1000, $dimmax[$key] * 1000));
            $total += $centroid[$key]*$centroid[$key];
    }
    $centroid = Kmeans::normaliseValue($centroid, sqrt($total));
    return $centroid;
  }

  /* We're expecting normalised docs */
   public static function normaliseValue(array $vector, $total) {
    foreach($vector as $key => $value) {
      $vector[$key] = $value/$total;
    }
    return $vector;
  }

   public static function asignarCentroides($doc, $centroides){
    $mapping = array();
    foreach ($doc as $id => $vector) {
      $short = 0;
      $cluster = null;
      foreach ($centroides as $key => $centroid) {
        $dist = 0;
        $total = Kmeans::norma($vector)*Kmeans::norma($centroid);
        foreach ($vector as $idword => $word) {
            if(isset($centroid[$idword])){
                $dist += $centroid[$idword]*$word;
            }
        }
        $dist = $dist/$total;
        if($dist >= $short){
          $short = $dist;
          $cluster = $key;
        }
      }
      $mapping[$id] = $cluster;
    }
    return $mapping;
  }

   public static function norma($vector){
    $total = 0;
    foreach ($vector as $key => $value) {
      $total = $total + ($value*$value);
    }
    $total= sqrt($total);
    return($total);
  }

   public static function actualizarCentroides($mapping, $doc, $k){
    $centroides = array();
    $denominador = array_count_values($mapping);
    foreach ($mapping as $key => $value) {
      foreach ($doc[$key] as $idword => $valor) {
        if(!isset($centroides[$value][$idword])){
          $centroides[$value][$idword] = 0;
        }
        $centroides[$value][$idword] += $valor/$denominador[$value];
      }
    }
    if(count($centroides) < $k) {
      $centroides = array_merge($centroides,Kmeans::initialiseCentroids($doc, $k - count($centroides)));
    }
    return $centroides;
  }
}
