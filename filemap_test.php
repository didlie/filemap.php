<?php
set_time_limit(60);
$lines = 1000000;
$file = "test.file";
$ft = microtime(true);
//createNewFile($file,$lines); die("<h4> Time to create a new file of {$lines} was " . (microtime(true) - $ft) . "</h4>");
          function createNewFile($file,$l){
            set_time_limit(0);
            filemap::generateTestFile($file,$l);
            exit();
          }

///use of the function above stops operation
echo "<pre><h1>filemap class tests</h1>";//extra pre open tag to make everything look mono



$filemap = new filemap($file);
$T1 = time();
/********************** test calibration ******************************/
// echo "<pre>" . json_encode($filemap->getCalibrationSettings()) . "</pre>";
// $filemap->autoCalibrate();
echo "<pre>Callibrating filemap class with line #1:<br>" . $filemap->getCalibrationLine() . "</pre>";
// echo "<pre>" . json_encode($filemap->getCalibrationSettings()) . "</pre>";
// exit();
/************************ end of test calibration *********************/
raceLines($filemap);
        function raceLines($filemap){
        echo "<h3>Race all lines time test, with optional operation call</h3>";
            $t1 = microtime(true);
            $out = $filemap->operateAllLines();
        echo "<pre>...done<br>completed in " . (microtime(true) - $t1) . " seconds on " . $out[0] . " lines<br>Last line is:<br>{$out[1]}</pre>";
        echo "<pre>This shows 1000001 because of an extra line ending in the file.</pre>";
        }


/****** test searc functions ************/
termSearchInFile($filemap);

            function termSearchInFile($filemap){
              $searchTime = microtime(true);
              $search = "Aa";//find this text//should go through entire file
              $string = $filemap->getFirstLineWithString($search);
              if($string !== ""){
                  echo "<h2>Running a line-by-line term search</h2>";
                  echo "<pre>First line with '{$search}' is:<br>" . $string . "</pre>";
                  echo "<pre> '{$search}' found at position " . strpos($string,$search) . "</pre>";
                  echo "<pre> we are at line number " . $filemap->getLineNumber() . "</pre>";
                  echo "<pre> ... completed in " . (microtime(true) - $searchTime) . " seconds.</pre>";
              }
              echo "<br>";
              $searchTime = microtime(true);
              $string = $filemap->getLastLineWithString($search);
              if($string !== ""){
                    echo "<h2>Running a reverse line-by-line term search</h2>";
                    echo "<pre>Last line found with '{$search}' is:<br>" . $string . "</pre>";
                    echo "<pre> '{$search}' found at position " . strpos($string,$search) . "</pre>";
                    echo "<pre> we are at line number " . $filemap->getLineNumber() . "</pre>";
                    echo "<pre> ... completed in " . (microtime(true) - $searchTime) . " seconds.</pre>";
                }
            }
echo "<h2>Extracting specific lines by line number</h2><h4>starting indexes are 1 forward, and reverse is -1</h4>";
getLineNumber(36,$filemap);
getLineNumber(-19,$filemap);
getLineNumber(1,$filemap);
getLineNumber(0,$filemap);
getLineNumber(-1,$filemap);
getLineNumber(100000,$filemap);
getLineNumber(1000000,$filemap);

        function getLineNumber($lineNum,$filemapObj){
          echo "<h2>Find line number {$lineNum}</h2>";
          $tt = microtime(true);
          echo "<pre>" . $filemapObj->getLineByNumber($lineNum) . "</pre>";
          echo "<pre> we are at line number " . $filemapObj->getLineNumber() . "</pre>";
          echo "<pre>...took time : " . (microtime(true) - $tt) . " seconds.</pre>";
        }

echo "<h2>First Line</h2>";
$t1 = microtime(true);
echo "<pre>" . $filemap->getFirstLine() . "</pre>";
echo "<pre> we are at line number " . $filemap->getLineNumber() . "</pre>";
echo "<pre>In " . (microtime(true) - $t1) . " seconds</pre>";

echo "<h2>Last Line</h2>";
$t1 = microtime(true);
echo "<pre>" . $filemap->getLastLine() . "</pre>";
echo "<pre> we are at line number " . $filemap->getLineNumber() . "</pre>";
echo "<pre>In " . (microtime(true) - $t1) . " seconds</pre>";




loopForward($filemap);


          function loopForward($filemapObj){
          echo "<h2>Forward</h2>";
            $i=0;
            $t1 = microtime(true);
            $filemapObj->getFirstLine();

            while(!$filemapObj->endOfFile()){
            $i++;
              $filemapObj->getNextLine();
              //validateLength($string,$filemapObj->standardLineLength);
            }
            echo "<pre>Null line returned at end of file: " . $filemapObj->getLine() . "</pre>";
            echo "<pre> we are at line number " . $filemapObj->getLineNumber() . "</pre>";
            echo "<sub>Execution Time: " . (microtime(true) - $t1) . " on " . $i . " lines.</sub>";
            echo "<h2>Loop forward with strpos() search on each line</h2>";
            $i=0;
            $t1 = microtime(true);
            $string = $filemapObj->getFirstLine();
            while(!$filemapObj->endOfFile()){
            $i++;
              $string = trim($filemapObj->getNextLine());
              $a = strpos($string,"Aaa");
            }
            echo "<pre>Null line returned at end of file: " . $filemapObj->getLine() . "</pre>";
            echo "<pre> we are at line number " . $filemapObj->getLineNumber() . "</pre>";
            echo "<sub>Execution Time: " . (microtime(true) - $t1) . " on " . $i . " lines.</sub>";

          }



loopBack($filemap);
loopBackWithStrpos($filemap);
getLineNumber(-201858,$filemap);
          function loopBack($filemapObj){
            echo "<h2>Now Backward</h2>";

                      $t1 = microtime(true);
                      $i=0;
                        $oldString = $string = $filemapObj->getLastLine();
                    $i=-1;
                        while($i > -1000000){

                            validateLength($string,$filemapObj->standardLineLength);
                            if($i > -5) echo "<sub>" . $string . "<br>Line {$i}; time from start: " . (microtime(true) - $t1) . " seconds </sub>";
                      //    echo "<pre>" . $string . "</pre>";
                          $string = trim($filemapObj->getPreviousLine());
                          //if($string === $oldString) die("loop backwards iteration failure");
                          $oldString = $string;
                          $i--;
                      }
                      echo "<pre> we are at line number " . $filemapObj->getLineNumber() . "</pre>";
              echo "<h4>Backward Execution Time:" . (microtime(true) - $t1) . " on {$i} lines.</h4>";
          }
            function loopBackWithStrpos($filemapObj){
              echo "<h2>Now Backward with if on strpos() search on each line</h2>";

                        $t1 = microtime(true);
                        $i=0;
                          $oldString = $string = $filemapObj->getLastLine();
                      $i=-1;
                          while($i > -1000000){

                              validateLength($string,$filemapObj->standardLineLength);
                              if($i > -5) echo "<sub>" . $string . "<br>Line {$i}; time from start: " . (microtime(true) - $t1) . " seconds </sub>";
                        //    echo "<pre>" . $string . "</pre>";
                            $string = trim($filemapObj->getPreviousLine());

                            $i--;
                            if($off = strpos($string,"XXXx")) echo "<br>'XXXx' found at {$i} at an offset of " . $off. "<br>" . "<pre>$string</pre>";
                            //if($string === $oldString) die("loop backwards iteration failure");
                            $oldString = $string;
                        }
                        echo "<pre> we are at line number " . $filemapObj->getLineNumber() . "</pre>";
                echo "<h4>Backward Execution Time:" . (microtime(true) - $t1) . " on {$i} lines.</h4>";
          }



////////////////////////////////////////

echo "<pre>";
echo "<div>Full file too large to display.</div>";
//var_dump(file($file,FILE_IGNORE_NEW_LINES));
echo "</pre>";


function validateLength($string,$standard){
    if($string && strlen($string) != $standard)die("Error in return string length " . strlen($string) . " != " . $standard);
}

function getLineNumberFromBlock($block){
    return (int)preg_replace('/[^\-\d]*(\-?\d*).*/','$1',$block) - 1;
}
