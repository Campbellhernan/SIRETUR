<?php

namespace App\Custom;

class Stemmer {

  public static function is_vowel($c) {
    return ($c == 'a' || $c == 'e' || $c == 'i' || $c == 'o' || $c == 'u' || $c == 'á' || $c == 'é' ||
      $c == 'í' || $c == 'ó' || $c == 'ú');
  }

  public static function getNextVowelPos($word, $start = 0) {
    $len = strlen($word);
    for ($i = $start; $i < $len; $i++)
      if (Stemmer::is_vowel($word[$i])) return $i;
    return $len;
  }

  public static function getNextConsonantPos($word, $start = 0) {
    $len = strlen($word);
    for ($i = $start; $i < $len; $i++)
      if (!Stemmer::is_vowel($word[$i])) return $i;
    return $len;		
  }

  public static function endsin($word, $suffix) {
    if (strlen($word) < strlen($suffix)) return false;
    return (substr($word, -strlen($suffix)) == $suffix);
  }

  public static function endsinArr($word, $suffixes) {
    foreach ($suffixes as $suff) {
      if (Stemmer::endsin($word, $suff)) return $suff;
    }
    return '';
  }

  public static function removeAccent($word) {
    return str_replace(array('á','é','í','ó','ú'), array('a','e','i','o','u'), $word);
  }

  public static function stemm($word) {
    $len = strlen($word);
    if ($len <=2) return $word;

    $word = strtolower($word);

    $r1 = $r2 = $rv = $len;
    //R1 is the region after the first non-vowel following a vowel, or is the null region at the end of the word if there is no such non-vowel.
    for ($i = 0; $i < ($len-1) && $r1 == $len; $i++) {
      if (Stemmer::is_vowel($word[$i]) && !Stemmer::is_vowel($word[$i+1])) { 
        $r1 = $i+2;
      }
    }

    //R2 is the region after the first non-vowel following a vowel in R1, or is the null region at the end of the word if there is no such non-vowel. 
    for ($i = $r1; $i < ($len -1) && $r2 == $len; $i++) {
      if (Stemmer::is_vowel($word[$i]) && !Stemmer::is_vowel($word[$i+1])) { 
        $r2 = $i+2; 
      }
    }

    if ($len > 3) {
      if(!Stemmer::is_vowel($word[1])) {
        // If the second letter is a consonant, RV is the region after the next following vowel
        $rv = Stemmer::getNextVowelPos($word, 2) +1;
      } elseif (Stemmer::is_vowel($word[0]) && Stemmer::is_vowel($word[1])) { 
        // or if the first two letters are vowels, RV is the region after the next consonant
        $rv = Stemmer::getNextConsonantPos($word, 2) + 1;
      } else {
        //otherwise (consonant-vowel case) RV is the region after the third letter. But RV is the end of the word if these positions cannot be found.
        $rv = 3;
      }
    }

    $r1_txt = substr($word,$r1);
    $r2_txt = substr($word,$r2);
    $rv_txt = substr($word,$rv);

    $word_orig = $word;

    // Step 0: Attached pronoun
    $pronoun_suf = array('me', 'se', 'sela', 'selo', 'selas', 'selos', 'la', 'le', 'lo', 'las', 'les', 'los', 'nos');	
    $pronoun_suf_pre1 = array('endo', 'ando', 'ar', 'er', 'ir');	
    $pronoun_suf_pre2 = array('ando', 'iendo', 'ar', 'er', 'ir');
    $suf = Stemmer::endsinArr($word, $pronoun_suf);
    if ($suf != '') {
      $pre_suff = Stemmer::endsinArr(substr($rv_txt,0,-strlen($suf)),$pronoun_suf_pre1);
      if ($pre_suff != '') {
        $word = Stemmer::removeAccent(substr($word,0,-strlen($suf)));
      } else {
        $pre_suff = Stemmer::endsinArr(substr($rv_txt,0,-strlen($suf)),$pronoun_suf_pre2);
        if ($pre_suff != '' ||
          (Stemmer::endsin($word, 'yendo' ) && 
          (substr($word, -strlen($suf)-6,1) == 'u'))) {
            $word = substr($word,0,-strlen($suf));
          }
      }
    }

    if ($word != $word_orig) {
      $r1_txt = substr($word,$r1);
      $r2_txt = substr($word,$r2);
      $rv_txt = substr($word,$rv);
    }
    $word_after0 = $word;

    if (($suf = Stemmer::endsinArr($r2_txt, array('anza', 'anzas', 'ico', 'ica', 'icos', 'icas', 'ismo', 'ismos', 'able', 'ables', 'ible', 'ibles', 'ista', 'istas', 'oso', 'osa', 'osos', 'osas', 'amiento', 'amientos', 'imiento', 'imientos'))) != '') {
      $word = substr($word,0, -strlen($suf));	
    } elseif (($suf = Stemmer::endsinArr($r2_txt, array('icadora', 'icador', 'icacion', 'icadoras', 'icadores', 'icaciones', 'icante', 'icantes', 'icancia', 'icancias', 'adora', 'ador', 'acion', 'adoras', 'adores', 'aciones', 'ante', 'antes', 'ancia', 'ancias'))) != '') {
      $word = substr($word,0, -strlen($suf));	
    } elseif (($suf = Stemmer::endsinArr($r2_txt, array('logia', 'logias'))) != '') {
      $word = substr($word,0, -strlen($suf)) . 'log';
    } elseif (($suf = Stemmer::endsinArr($r2_txt, array('ucion', 'uciones'))) != '') {
      $word = substr($word,0, -strlen($suf)) . 'u';
    } elseif (($suf = Stemmer::endsinArr($r2_txt, array('encia', 'encias'))) != '') {
      $word = substr($word,0, -strlen($suf)) . 'ente';
    } elseif (($suf = Stemmer::endsinArr($r2_txt, array('ativamente', 'ivamente', 'osamente', 'icamente', 'adamente'))) != '') {
      $word = substr($word,0, -strlen($suf));
    } elseif (($suf = Stemmer::endsinArr($r1_txt, array('amente'))) != '') {
      $word = substr($word,0, -strlen($suf));
    } elseif (($suf = Stemmer::endsinArr($r2_txt, array('antemente', 'ablemente', 'iblemente', 'mente'))) != '') {
      $word = substr($word,0, -strlen($suf));
    } elseif (($suf = Stemmer::endsinArr($r2_txt, array('abilidad', 'abilidades', 'icidad', 'icidades', 'ividad', 'ividades', 'idad', 'idades'))) != '') {
      $word = substr($word,0, -strlen($suf));
    } elseif (($suf = Stemmer::endsinArr($r2_txt, array('ativa', 'ativo', 'ativas', 'ativos', 'iva', 'ivo', 'ivas', 'ivos'))) != '') {
      $word = substr($word,0, -strlen($suf));
    }

    if ($word != $word_after0) {
      $r1_txt = substr($word,$r1);
      $r2_txt = substr($word,$r2);
      $rv_txt = substr($word,$rv);
    }
    $word_after1 = $word;

    if ($word_after0 == $word_after1) {
      // Do step 2a if no ending was removed by step 1. 
      if (($suf = Stemmer::endsinArr($rv_txt, array('ya', 'ye', 'yan', 'yen', 'yeron', 'yendo', 'yo', 'yas', 'yes', 'yais', 'yamos'))) != '' && (substr($word,-strlen($suf)-1,1) == 'u')) {
        $word = substr($word,0, -strlen($suf));
      }

      if ($word != $word_after1) {
        $r1_txt = substr($word,$r1);
        $r2_txt = substr($word,$r2);
        $rv_txt = substr($word,$rv);
      }
      $word_after2a = $word;

      // Do Step 2b if step 2a was done, but failed to remove a suffix. 
      if ($word_after2a == $word_after1) {
        if (($suf = Stemmer::endsinArr($rv_txt, array('en', 'es', 'eis', 'emos'))) != '') {
          $word = substr($word,0, -strlen($suf));
          if (Stemmer::endsin($word, 'gu')) {
            $word = substr($word,0,-1);
          }
        } elseif (($suf = Stemmer::endsinArr($rv_txt, array('arian', 'arias', 'aran', 'aras', 'ariais', 'aria', 'areis', 'ariamos', 'aremos', 'ara', 'are', 'erian', 'erias', 'eran', 'eras', 'eriais', 'eria', 'ereis', 'eriamos', 'eremos', 'era', 'ere', 'irian', 'irias', 'iran', 'iras', 'iriais', 'iria', 'ireis', 'iriamos', 'iremos', 'ira', 'ire', 'aba', 'ada', 'ida', 'ia', 'ara', 'iera', 'ad', 'ed', 'id', 'ase', 'iese', 'aste', 'iste', 'an', 'aban', 'ian', 'aran', 'ieran', 'asen', 'iesen', 'aron', 'ieron', 'ado', 'ido', 'ando', 'iendo', 'io', 'ar', 'er', 'ir', 'as', 'abas', 'adas', 'idas', 'ias', 'aras', 'ieras', 'ases', 'ieses', 'is', 'ais', 'abais', 'iais', 'arais', 'ierais', '  aseis', 'ieseis', 'asteis', 'isteis', 'ados', 'idos', 'amos', 'abamos', 'iamos', 'imos', 'aramos', 'ieramos', 'iesemos', 'asemos'))) != '') {
          $word = substr($word,0, -strlen($suf));
        }
      }
    }

    // Always do step 3. 
    $r1_txt = substr($word,$r1);
    $r2_txt = substr($word,$r2);
    $rv_txt = substr($word,$rv);

    if (($suf = Stemmer::endsinArr($rv_txt, array('os', 'a', 'o', 'a', 'i', 'o'))) != '') {
      $word = substr($word,0, -strlen($suf));
    } elseif (($suf = Stemmer::endsinArr($rv_txt ,array('e','e'))) != '') {
      $word = substr($word,0,-1);
      $rv_txt = substr($word,$rv);
      if (Stemmer::endsin($rv_txt,'u') && Stemmer::endsin($word,'gu')) {
        $word = substr($word,0,-1);
      }
    }
    return Stemmer::removeAccent($word);
  }
}
