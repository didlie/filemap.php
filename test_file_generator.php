<?php
/********** static test file generator class ***************/
/******** isaac krishna deilson jacobs christopher *********/
/*************** all rights reserved ***********************/

class test_file_generator
{
    /****************************** static loops **************************************************************/
                public static function generateTestFile($path="test.file",$numLines = 10){
                  //this could potentially be forked and written faster
                      $hand = fopen($path,"w");
                      for($i=$numLines;$i>0;$i--){
                        $line = rtrim(self::generateTestLine());
                        fwrite($hand,$line . PHP_EOL);
                        unset($line);
                      }
                      fclose($hand);
                      return $path;
                }//////////////////
                                  private static function generateTestLine(){
                                    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
                                    $l = strlen($chars) - 1;
                                    $s = str_split($chars);
                                    $x = "";
                                    for($i=0;$i<256;$i++){
                                      $j = random_int(0,$l);
                                      shuffle($s);
                                      $x .= $s[$j];
                                    }
                                    return $x;
                                  }
}
