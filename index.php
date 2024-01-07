<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once 'src/init.php';

use Bot\Exceptions\{
    CommonException,
    CurlException,
    TeleBotException
};
use Bot\Util\{
    Logger,
    InputUser
};
use Bot\TelegramBot\CurlPost\CurlPostFieldBuilder\{
    CurlPostFieldHtmlBuilder,
    CurlPostFieldAdminBuilder,
    CurlPostFieldMdBuilder
};
use Bot\TelegramBot\CommandStrategy\ContextCommand;
use Bot\TelegramBot\CommandStrategy\Commands\{
    StartCommand,
    WeatherCurrentCommand,
    WeatherForecastCommand,
    TimeCommand
};
use Bot\TelegramBot\TelegramBot;

use Bot\Facades\Commands\{Start, Time, Weather, Forecast, UnknownCommand};

$config = parse_ini_file('config.ini');

$data = json_decode(file_get_contents('php://input'));

$fromChatId = InputUser::fromChatId($data);

$messageText = InputUser::getInput($data);

$ErrLogger = new Logger('/Logs', '/errLogs.txt');
$logger = new Logger();

//$logger->writeLog($messageText);
//$logger->writeLog($messageText, true);
//$logger->writeLog($data->callback_query->from->id);



$telegramBot = new TelegramBot($config);

$sendMessageCurlPostFieldAdmin = (new CurlPostFieldAdminBuilder())
    ->init()
    ->setChatId($config['adminId'])
    ->setParse_mode('html')
    ->build();

$contextCommand = new ContextCommand();

//$messageText = '/start';
try {
    switch ($messageText) {
        case '/start':
/**
            $contextCommand->setStrategy(new StartCommand($data));

            $helloTextMessage = $contextCommand->executeStrategy();
            
            $sendMessageCurlPostField = (new CurlPostFieldHtmlBuilder())
            ->init()
            ->setChatId($fromChatId)
            ->setParse_mode('html')
            ->setText($helloTextMessage)
            ->build();

            $curlOpt = $sendMessageCurlPostField->getOpt();

            $telegramBot->sendResponseTelegram('sendMessage', $curlOpt);
 
 */
            
            Start::startCommand($data, $config);
            
            break;

        case '/time':
            /**
            $contextCommand->setStrategy(new TimeCommand());

            $timeTextMessage = $contextCommand->executeStrategy();


            $sendMessageCurlPostField = (new CurlPostFieldHtmlBuilder())
                ->init()
                ->setChatId($fromChatId)
                ->setParse_mode('html')
                ->setText($timeTextMessage)
                ->build();

            $curlOpt = $sendMessageCurlPostField->getOpt();

            $telegramBot->sendResponseTelegram('sendMessage', $curlOpt);
            */
            
            Time::timeCommand($data, $config);
            
            break;

        case '/weather':
            /**
            $contextCommand->setStrategy(new WeatherCurrentCommand());
            
            $weathetTextMessage = $contextCommand->executeStrategy();
            
            $inlineKeyboard = [
                [
                    'text' => 'Прогноз на 3 дня',
                    'callback_data' => 'forecast',
                ]
            ];

            $sendMessageCurlPostField = (new CurlPostFieldHtmlBuilder())
                ->init()
                ->setChatId($fromChatId)
                ->setParse_mode('html')
                ->setText($weathetTextMessage)
                ->setReplyMarkup('inline_keyboard', $inlineKeyboard)
                ->build();

            $curlOpt = $sendMessageCurlPostField->getOpt();

            $telegramBot->sendResponseTelegram('sendMessage', $curlOpt);
            */
            
            Weather::weatherCommand($data, $config);
            
            break;

        case 'forecast':
            /**
            $contextCommand->setStrategy(new WeatherForecastCommand());
            
            $weathetTextMessage = $contextCommand->executeStrategy();
            
            $sendMessageCurlPostField = (new CurlPostFieldHtmlBuilder())
            ->init()
            ->setChatId($fromChatId)
            ->setParse_mode('html')
            ->setText($weathetTextMessage)
            ->build();

            $curlOpt =  $sendMessageCurlPostField->getOpt();

            $telegramBot->sendResponseTelegram('sendMessage', $curlOpt);
            */
            
            Forecast::forecastCommand($data, $config);
            
            break;

        default:
            /**
            $sendMessageCurlPostField = (new CurlPostFieldHtmlBuilder())
                ->init()
                ->setChatId($fromChatId)
                ->setParse_mode('html')
                ->setText(
                    "Неизвестная команда." . PHP_EOL
                    . "Выберите команду из списка в меню.")
                ->build();

            $curlOpt = $sendMessageCurlPostField->getOpt();

            $telegramBot->sendResponseTelegram('sendMessage', $curlOpt);
             
             */
            
            UnknownCommand::unknownCommand($data, $config);
            
            break;
    }
} catch (TeleBotException $e) {
    $ErrLogger->writeLog($e->sendErrorMessage());

    $sendMessageCurlPostFieldAdmin->setMessage($e->sendErrorMessage());

    $curlOpt = $sendMessageCurlPostFieldAdmin->getOpt();
    $telegramBot->sendResponseTelegram('sendMessage', $curlOpt);
} catch (CurlException $e) {
    $ErrLogger->writeLog($e->sendErrorMessage());

    $sendMessageCurlPostFieldAdmin->setMessage($e->sendErrorMessage());
    $curlOpt = $sendMessageCurlPostFieldAdmin->getOpt();

    $telegramBot->sendResponseTelegram('sendMessage', $curlOpt);
} catch (CommonException $e) {
    $message = $e->getTraceAsString() . PHP_EOL . $e->getMessage();
    $ErrLogger->writeLog($message);

    $sendMessageCurlPostFieldAdmin->setMessage($message);
    $curlOpt = $sendMessageCurlPostFieldAdmin->getOpt();

    $telegramBot->sendResponseTelegram('sendMessage', $curlOpt);
}