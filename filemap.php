<?php
/***********************************************************/
/********************* didlie.com **************************/
/********** file line iteration and search class ***********/
/*auth:*** isaac krishna deilson jacobs christopher ********/
/*************** all rights reserved according to:**********/
/*********** GNU General Public License v3.0 ***************/
/***********************************************************/

ini_set("auto_detect_line_endings", true);

class filemap
{
    /******* memory management variables are set by autoCalibrate() *********/
    private $depthBufferSize = 200;//default
    private $bufferCalibrationMultiplier = 2.06;//2.06 works best for 1mill lines of 255bit data
    private $memMax = 4096;//bites, 1k = 1024?, manually adjustable value
    private $calibrated = false;
    private $calibrationLine = "";//only has value when calibrated;
            public $standardLineLength;//length of calibration line , public

    /******* run-time variables ***********/
    private $pointer = NULL;// fopen file resource
    private $pointerPosition = 0;// reset in reset()
    /*** construct values ****/
    private $path = "";
    private $fileSize = 0;
    /*** more runtime variables adjusted by function calls ****/
    private $line = "";// reset in reset()
    private $lineNumber = NULL;// reset in reset(), may be positive, or negative offset from end of file, or null
    private $lastLinePosition = NULL;// reset in reset()
    private $readLength;//used in reverse search // reset in reset()

    /************ optional error handling ******************/
                            // private $errors = [];
                            //
                            // public function errors(){
                            //   return $this->errors;
                            // }
                            //
                            // public function errorsExist(){
                            //   return (count($this->errors) > 0)? true : false;
                            // }
/***********************************************************************************/
                            public function endOfFile(){
                                return (feof($this->pointer))? true : false;
                            }
                            public function getLine(){
                                return $this->line;
                            }
                            public function getLineNumber(){
                                if(NULL !== $this->lineNumber) return $this->lineNumber;
                                return false;
                            }
                            public function getFileSize(){
                              return $this->fileSize;
                            }

                            public function getCalibrationSettings(){
                              return ['memMax'=>$this->memMax,'depthBufferSize'=>$this->depthBufferSize];
                            }

                            public function autoCalibrate(){
                                if($this->calibrated) return;
                              $this->getFirstLine();
                              $this->calibrationLine = trim($this->line);
                              $this->standardLineLength = strlen($this->calibrationLine);
                              $this->depthBufferSize =
                                        intval($this->bufferCalibrationMultiplier * $this->standardLineLength);//adjustment
                              $this->memMax = 4 * $this->depthBufferSize;
                              $this->calibrated = true;
                              $this->reset();
                            }

                            public function getCalibrationLine(){
                              $this->autoCalibrate();
                              return $this->calibrationLine;
                            }

                public function reset(){
                    rewind($this->pointer);
                  $this->pointerPosition = 0;
                  $this->lineNumber = NULL;
                  $this->line = "";
                  $this->readLength = $this->memMax;
                  $this->lastLinePosition = 0;
                }
/********************************************************************************/
/*************** CONSTRUCT ******************************************************/
/********************************************************************************/

    public function __construct($path,$write=false){
        $this->path = $path;
        $this->fileSize = filesize($this->path);
        if(!is_readable($path)) error_handler::$errors[] = "Invalid path in filemap.";//comment out for epoch block creation
        if($write === true){
            $this->getFlockWritePointer();//locks the file for writing
        }else{
            $this->getReadPointer();
        }
          if(error_handler::errorsExist()){
            error_handler::getErrors();
          }
    }
///////////////////////////////////////////
    public function positionPointer($bits){
      fseek($this->pointer,$bits);
    }
/******************************GET READ AND WRITE POINTERS FOPEN *****************************************/
                                private function getReadPointer(){
                                    if($this->pointer === NULL){
                                      if($this->pointer = fopen($this->path,'r+b')){
                                        return $this->pointer;
                                      }else{
                                        $t = time();
                                        while((time() - $t) < 10){
                                          if($this->pointer = fopen($this->path,'r+b')) return $this->pointer;
                                        }
                                      }
                                    }else{
                                        return $this->pointer;
                                    }
                                    error_handler::$errors[] = "Unable to gain read access for filemap";
                                }////////////////////////////////
//////////////////////////////////////////////////////////////////
                              private function getFlockWritePointer(){
                                //a+ writes are always appended
                                if($this->pointer === NULL){
                                  if($this->pointer = fopen($this->path,'a+b')){
                                    if(flock($this->pointer,LOCK_EX | LOCK_NB)) return $this->pointer;
                                  }else{
                                    $t = time();
                                    while((time() - $t) < 10){
                                      if($this->pointer = fopen($this->path,'a+b')){
                                        if(flock($this->pointer,LOCK_EX | LOCK_NB)) return $this->pointer;
                                      }
                                    }
                                  }
                                }else{
                                    return $this->pointer;
                                }
                                error_handler::$errors[] = "Unable to get write access for filemap";
                              }
///////////////////////////////////////////////////////
            public function write($line,$length=4096){
              //no fseek required; handle is oppened in "a+"
              $line = $line . PHP_EOL;
              $return = fwrite($this->pointer,$line,$length);
              if(!$return){
                error_handler::$errors[] = "Unable to write to file: {$this->path}";
                return false;
              }else{
                return $return;
              }
            }
/////////////////////////////////////////////////////
            public function closeFlockPointer(){
              flock($this->pointer, LOCK_UN);
                fclose($this->pointer);
                  return true;// used to close in validation in wallet
            }
/***************************************** word search functions *****************************************/
//forward
      public function getFirstLineWithString($string){
        $this->getFirstLine();
        while($this->line != ""){
          if(strpos($this->line,$string) !== false){
            return $this->line;
          }
          $this->getNextLine();
        }
        $this->reset();
        return "";
      }

//backward
      public function getLastLineWithString($string){
        $this->getLastLine();
        while($this->line != false){
          if(strpos($this->line,$string) !== false){
            return $this->line;
            }
          $this->getPreviousLine();
        }
        $this->reset();
        return "";
      }

/************************************* forward line retrieval ********************************************/
        public function operateAllLines($operation=NULL){
            $i=0;
            $this->reset();
            fseek($this->pointer,0,SEEK_SET);
            do{
                $line = trim(stream_get_line($this->pointer,$this->memMax,PHP_EOL));
                if($operation) $operation($line);
                $i++;
            }while(!feof($this->pointer));
            return [$i,$line];
        }

        public function getFirstLine(){
            $this->reset();
            $this->lineNumber = 0;
            $this->getNextLine();
            return $this->line;
        }
///////////////////////////////////////////
        public function getNextLine(){
            $this->line = "";
            if($this->pointerPosition !== $this->lastLinePosition){
                fseek($this->pointer,$this->pointerPosition,SEEK_SET);
            }
            if(!feof($this->pointer)){
                $this->line = trim(stream_get_line($this->pointer,$this->memMax,PHP_EOL));
                //$this->line = fgets($this->pointer,$this->memMax);
                if(feof($this->pointer) && $this->line === ""){
                    return false;
                }else{
                    $this->lastLinePosition = $this->pointerPosition = ftell($this->pointer);
                    //$this->lastLinePosition = ($this->pointerPosition += strlen($this->line));
                    $this->lineNumber++;
                    return $this->line;
                }
            }
            return $this->line;
        }
/************* get lines if you know the line number, forward or backward ******************/
        public function getLineByNumber($num=1){
          $this->reset();
          if($num>=0){
              $this->getFirstLine();
              for($i=1;$i<$num;$i++){
                if(feof($this->pointer) === true){
                    $this->lineNumber = $i;
                    return false;
                }
                $this->getNextLine();
              }
              $this->lineNumber = $i;
              return $this->line;
          }else{
              $this->getLastLine();
              for($i=-1;$i>$num;$i--){
                if($this->pointerPosition > 0){
                    $this->getPreviousLine();
                }else{
                    break;
                }
              }
              $this->lineNumber = $i;
              return $this->line;
          }
        }

/************************************** backward line retrieval *******************************************/
                    public function getLastLine(){
                      $this->lastLinePosition = $this->pointerPosition = $this->fileSize;
                      $this->lineNumber = 0;
                      return $this->getPreviousLine();
                    }///////////////

                    public function getPreviousLine(){
                      $this->line = false;
                      $this->readLength = 0;
                          while(($this->line == false || $this->line == "") && $this->lastLinePosition > 0){
                             $this->readLength += $this->depthBufferSize;
                            $this->pointerPosition = max(0,($this->pointerPosition-$this->depthBufferSize));
                            fseek($this->pointer,$this->pointerPosition,SEEK_SET);
                            if($this->pointerPosition === 0){
                                echo "<sub>" . $this->line = trim(fgets($this->pointer,$this->memMax));
                                echo "</sub>";
                                $this->lastLinePosition = 0;
                                $this->pointerPosition = 0;
                                $this->lineNumber = 0;
                            }else{
                                //$chunk = fread($this->pointer,$this->readLength);
                                $chunk = stream_get_contents($this->pointer,$this->readLength);
                                if(substr_count($chunk,PHP_EOL) >= 2){
                                    $a = explode(PHP_EOL,$chunk);
                                    array_pop($a);
                                    $this->line = array_pop($a);
                                    $this->pointerPosition += strlen(implode($a));
                                    $this->lastLinePosition = $this->pointerPosition;
                                    $this->lineNumber--;
                                }
                            }
                                if($this->memMax < $this->readLength) die("mem limit exceeded in getPreviousLine, memMax=" . $this->memMax . " and readLength=" . $this->readLength);
                          }
                          return $this->line;
                    }

}
