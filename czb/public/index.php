<?php
use Phalcon\Di\FactoryDefault;

use Phalcon\Mvc\Micro;
use Phalcon\Http\Response;

error_reporting(E_ALL);

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

try {

	$di = new FactoryDefault();

	include APP_PATH . '/config/router.php';

	include APP_PATH . '/config/services.php';

	$config = $di->getConfig();

	include APP_PATH . '/config/loader.php';

    $app = new Micro($di);

    // Utiliza a API e através do método GET, RETORNA TODOS OS DADOS.
    $app->get(
        "/api/vagas",
        function () use ($app) {
            $phql = "SELECT * FROM Vagas";
            $dados = $app->modelsManager->executeQuery($phql);
            $data = [];
            foreach($dados as $dado){
                $data[] = [
                    'id'      => $dado->id,
                    'title'    => $dado->title,
                    'descript'  => $dado->descript,
                    'created_at'  => $dado->created_at,
                ];
            }
            echo json_encode($data);
        }
    );

    /*
    * Utiliza a API e através do método GET, RETORNA APENAS UM DADO.
    * Nota: Ele receberá como GET um ID que terá um tratamento através do ':[0-9]+' 
    * permitindo assim que o mesmo seja somente do tipo INTEIRO.
    */
    $app->get(
        "/api/vagas/{id:[0-9]+}",
        function ($id) use ($app) {
            $phql = "SELECT * FROM Vagas WHERE id = ".$id;
            $dados = $app->modelsManager->executeQuery($phql);
            $data = [];
            foreach($dados as $dado){
                $data[] = [
                    'id'      => $dado->id,
                    'title'    => $dado->title,
                    'descript'  => $dado->descript,
                    'created_at'  => $dado->created_at,
                ];
            }
            echo json_encode($data);
        }
    );

    // Utiliza a API e através do método POST, INSERE UM NOVO DADO.
    $app->post(
        "/api/vagas",
        function () use ($app) {

            $jsonData = $app->request->getJsonRawBody();

            $insert = [
                'title' 	 => $jsonData->title,
                'descript' => $jsonData->descript
            ];
            $phql = "INSERT INTO Vagas (title, descript) VALUES ('".$insert['title']."', '".$insert['descript']."')";
            echo $phql;
            $status = $app->modelsManager->executeQuery($phql);

            // cria response
            $response = new Response();
            if($status->success() === true){
                $response->setStatusCode(201, "Criado");
                $response->setJsonContent(
                    [
                        'status' => "ok"
                    ]
                );
            } else {
                $response->setStatusCode(409, "Conflito");
                $errors = [];
                foreach ($status->getMessages() as $msg){
                    $errors[] = $msg->getMessage();
                }
                $response->setJsonContent(
                    [
                        'status' => "Erro",
                        'messages' => $errors
                    ]
                );
            }
            return $response;
        }
    );

    // Utiliza a API e através do método PUT, ALTERA UM DADO EXISTENTE respeitando o tratamento do ID.
    $app->put(
        "/api/vagas/{id:[0-9]+}",
        function ($id) use ($app) {

            $jsonData = $app->request->getJsonRawBody();

            $update = [
                'id'		 => $id,
                'title' 	 => $jsonData->title,
                'descript' => $jsonData->descript,
            ];

            $phql = "UPDATE Vagas SET title = '".$update['title']."', descript = '".$update['descript']."' WHERE id = ".$id;
            $status = $app->modelsManager->executeQuery($phql);

            // cria response
            $response = new Response();
            if($status->success() === true){
                $response->setStatusCode(201, "Criado");
                $response->setJsonContent(
                    [
                        'status' => "ok"
                    ]
                );
            } else {
                $response->setStatusCode(409, "Conflito");

                $errors = [];

                foreach ($status->getMessages() as $msg){
                    $errors[] = $msg->getMessage();
                }

                $response->setJsonContent(
                    [
                        'status' => "Erro",
                        'messages' => $errors
                    ]
                );
            }
            return $response;
        }
    );

    // Utiliza a API e através do método DELETE, REMOVE UM DADO EXISTENTE respeitando o tratamento do ID.
    $app->delete(
        "/api/vagas/{id:[0-9]+}",
        function ($id) use ($app) {
            $phql = "DELETE FROM Vagas WHERE id = ".$id;
            $status = $app->modelsManager->executeQuery($phql);

            // cria response
            $response = new Response();
            if($status->success() === true){
                $response->setJsonContent(
                    [
                        'status' => "ok"
                    ]
                );
            } else {
                $response->setStatusCode(409, "Conflito");
                $erros = [];
                foreach ($status->getMessages() as $msg){
                    $erros[] = $msg->getMessage();
                }
                $response->setJsonContent(
                    [
                        'status' => "Erro",
                        'messages' => $erros
                    ]
                );
            }
            return $response;
        }
    );

    $app->handle($_SERVER['REQUEST_URI']);
} catch (\Exception $e) {
	echo $e->getMessage() . '<br>';
	echo '<pre>' . $e->getTraceAsString() . '</pre>';
}