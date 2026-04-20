<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

//Simple logger class for auditing log thing
class Logger
{

    //FOR STATIC FUNCTION U USE ::
    //lets say component will be the name of the component from where we are logging like quizResultApi or quizResultShowService or questionRepository, etc.

    //lets say message will be msg we want to log and want people to know what happened

    //lets say context will be array of extra info we will log like some imp info like quiz attempt id or anything that can help in debugging or anything else, default context will be empty array hehe

    //1. INFO for successful events!
    public static function logInfo(string $component,string $message,array $context=[]):void
    {
        //self keyword will be used to call static function from the same class
        self::writeLog('INFO',$component,$message,null,null,$context);
    }

    //2. WARN for some issues u run into
    //will have additional parameter for error code so we know what type of warning
    public static function logWarn(string $component,string $message,string $errorCode,array $context=[]):void
    {
        self::writeLog('WARN',$component,$message,$errorCode,null,$context);
    }

    //3. ERROR for some big issue
    //will have additional parameter for error code and also for exception if there is any exception that we want to log, default exception value will be null
    public static function logError(string $component,string $message,string $errorCode,?Throwable $exception=null,array $context=[]):void
    {
        self::writeLog('ERROR',$component,$message,$errorCode,$exception,$context);
    }

    //4. FATAL for crash situations
    //same as error now
    public static function logFatal(string $component,string $message,string $errorCode,?Throwable $exception=null,array $context=[]):void
    {
        self::writeLog('FATAL',$component,$message,$errorCode,$exception,$context);
    }


    //most imp function that will write the log 
    //it will have params such as level which will say info,warn etc, component for the particular file it is being written for, msg for log msg, error code ofc, exception, and any extra contexxt we need now
    private static function writeLog(string $level,string $component,string $message,?string $errorCode,?Throwable $exception,array $context):void
    {
        //we have to return array of all info present in bstd
        $logMessage=[
            'timestamp'=>gmdate('c'), //current time as per utc
            'level'=>$level, //log level like info,warn,error,fatal
            'service'=>LOG_SERVICE_NAME_DEFAULT, //default service name like quizApi hehe
            'component'=>$component, //which file does it belong to
            'correlationId'=>'00000', //idk what it is
            'message'=>$message, //log message that we want to log
            'errorCode'=>$errorCode, //error code if any error
            'durationMs'=>LOG_DURATION_DEFAULT_MS, //just keeping it deafult for now
            'stackTrace'=>$exception ? $exception->getTraceAsString() :null, //what stacktrace ?? getTraceAsString will give the stack trace of the exception if there is any exception otherwise it will be null 
            'context'=>$context //any additional info
        ];

        // error_log(json_encode($logMessage)); //log it in json format
        //send error messages to the web server's error log //just write it down!!!!!

        //now we should save it in another file
        $filePath=__DIR__ . '/../../logs/app.log';

        if (!file_exists($filePath))
        {
            touch($filePath); //create file if it doesnt exist
        }

        file_put_contents($filePath,json_encode($logMessage) . PHP_EOL, FILE_APPEND | LOCK_EX);

        //php_eol will give new line to each new log
        //file_append means it will append to existing and not override the logs
        //lock_ex means will not let file corrupt even if multiple requests write simultaneously
    }
}