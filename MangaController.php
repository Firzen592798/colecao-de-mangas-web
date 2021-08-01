
<?php
require "BaseController.php";
require "MangaModel.php";
require "Manga.php";
require "UsuarioModel.php";
require "Usuario.php";
class MangaController extends BaseController
{
    public function listAction()
    {
        $strErrorDesc = '';
        $requestMethod = $_SERVER["REQUEST_METHOD"];
        $arrQueryStringParams = $this->getQueryStringParams();
 
        if (strtoupper($requestMethod) == 'GET') {
            try {
                $mangaModel = new MangaModel();
 
                /*$intLimit = 10;
                if (isset($arrQueryStringParams['limit']) && $arrQueryStringParams['limit']) {
                    $intLimit = $arrQueryStringParams['limit'];
                }*/
 
                $arrMangas = $mangaModel->getMangas();
                $responseData = json_encode($arrMangas);
            } catch (Error $e) {
                $strErrorDesc = $e->getMessage().'Something went wrong! Please contact support.';
                $strErrorHeader = 'HTTP/1.1 500 Internal Server Error';
            }
        } else {
            $strErrorDesc = 'Method not supported';
            $strErrorHeader = 'HTTP/1.1 422 Unprocessable Entity';
        }
 
        // send output
        if (!$strErrorDesc) {
            $this->sendOutput(
                $responseData,
                array('Content-Type: application/json', 'HTTP/1.1 200 OK')
            );
        } else {
            $this->sendOutput(json_encode(array('error' => $strErrorDesc)), 
                array('Content-Type: application/json', $strErrorHeader)
            );
        }
    }

    public function postAction(){
        echo("manga");
        $strErrorDesc = '';
        $requestMethod = $_SERVER["REQUEST_METHOD"];
        
        $arrQueryStringParams = $this->getQueryStringParams();
        
        if (strtoupper($requestMethod) == 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            //var_dump($data);
            $manga = new Manga($data["id_usuario"], $data["chave"], $data["valor"]);
            $manga->novo = $data["novo"];
            $mangaModel = new MangaModel();
            $manga = $mangaModel->salvarOuAtualizar($manga);
            //var_dump($manga);
        }
    }

    public function cadastrarUsuarioAction(){
        $strErrorDesc = '';
        $requestMethod = $_SERVER["REQUEST_METHOD"];
        $arrQueryStringParams = $this->getQueryStringParams();
        
        if (strtoupper($requestMethod) == 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $usuario = new Usuario($data["email"], $data["senha"]);
            $usuarioModel = new UsuarioModel();
            $usuario = $usuarioModel->salvarOuAtualizar($usuario);
            
            $responseData = json_encode($usuario);
            $this->sendOutput(
                $responseData,
                array('Content-Type: application/json', 'HTTP/1.1 200 OK')
            );
        }
    }
}
?>