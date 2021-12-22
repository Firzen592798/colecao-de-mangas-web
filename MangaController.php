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
                $idUsuario = $_GET['usuario'];
                $mangaModel = new MangaModel();
                $arrMangas = $mangaModel->listMangasByUsuario($idUsuario);
                $mangaLista = array();
                $json = "[";
                $ultimoItem = end($arrMangas);
                foreach($arrMangas as $value){
                    if($ultimoItem != $value){
                        $json=$json . $value["valor"] . ",";
                    }else{
                        $json=$json . $value["valor"];
                    }
                }
                $json = $json . "]";
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
                $json,
                array('Content-Type: application/json', 'HTTP/1.1 200 OK')
            );
        } else {
            $this->sendOutput(json_encode(array('error' => $strErrorDesc)), 
                array('Content-Type: application/json', $strErrorHeader)
            );
        }
    }

    public function postAction(){
        $strErrorDesc = '';
        $requestMethod = $_SERVER["REQUEST_METHOD"];
        
        $arrQueryStringParams = $this->getQueryStringParams();
        
        if (strtoupper($requestMethod) == 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $manga = new Manga($data["id_usuario"], $data["chave"], $data["valor"]);
            $manga->novo = $data["novo"];
            $mangaModel = new MangaModel();
            $manga = $mangaModel->salvarOuAtualizar($manga);
            $responseData = json_encode($manga);
            $this->sendOutput(
                $responseData,
                array('Content-Type: application/json', 'HTTP/1.1 200 OK')
            );
        }
    }

    //Remove um mangá de um usuário
    public function removerAction(){
        $strErrorDesc = '';
        $requestMethod = $_SERVER["REQUEST_METHOD"];
        $arrQueryStringParams = $this->getQueryStringParams();
        if (strtoupper($requestMethod) == 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $mangaModel = new MangaModel();
            $response = $mangaModel->deletarByUsuarioIdAndKey($data["id_usuario"], $data["chave"]);
            $responseData = '{"linhasAfetadas": ' . $response .'}';
            $this->sendOutput(
                $responseData,
                array('Content-Type: application/json', 'HTTP/1.1 200 OK')
            );
        }
    }

    public function sincronizarNaEntradaAction(){
        //echo("PHP");
        $strErrorDesc = '';
        $requestMethod = $_SERVER["REQUEST_METHOD"];
        
        $arrQueryStringParams = $this->getQueryStringParams();
        
        if (strtoupper($requestMethod) == 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $mangaLista = array();
            $idUsuario = $data["usuario"];
            foreach($data["dados"] as $value){
                $manga = new Manga($idUsuario, $value["key"], $value);
                array_push($mangaLista, $manga);
            }
            $mangaModel = new MangaModel();
            $linhasAfetadas = $mangaModel->sincronizarNaEntrada($mangaLista);
            $responseData = '{"linhasAfetadas": ' . $linhasAfetadas .'}';
            $this->sendOutput(
                $responseData,
                array('Content-Type: application/json', 'HTTP/1.1 200 OK')
            );
        }
    }

    //Roda quando um usuário acaba de se cadastrar e precisa salvar tudo que tem no banco de dados online de uma vez
    public function salvarEmLoteAction(){
        $strErrorDesc = '';
        $requestMethod = $_SERVER["REQUEST_METHOD"];
        $arrQueryStringParams = $this->getQueryStringParams();
        if (strtoupper($requestMethod) == 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $mangaLista = array();
            $idUsuario = $data["usuario"];
            foreach($data["dados"] as $value){
                $manga = new Manga($idUsuario, $value["key"], $value);
                array_push($mangaLista, $manga);
            }
            $mangaModel = new MangaModel();
            $linhasInseridas = $mangaModel->salvarEmLote($mangaLista);
            $responseData = '{"linhasInseridas": ' . $linhasInseridas .'}';
            $this->sendOutput(
                $responseData,
                array('Content-Type: application/json', 'HTTP/1.1 200 OK')
            );
        }
    }

    public function loginAction(){
        $strErrorDesc = '';
        $requestMethod = $_SERVER["REQUEST_METHOD"];
        $arrQueryStringParams = $this->getQueryStringParams();
        
        if (strtoupper($requestMethod) == 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $email = $data["email"];
            $senha = $data["senha"];
            $usuarioModel = new UsuarioModel();
            $usuarioLista = $usuarioModel->login($email, $senha);
            if($usuarioLista){
                $usuario = $usuarioLista[0];
                $responseData = '{"idUsuario": ' . $usuario["id_usuario"] .', "email": "' . $usuario["email"] .'"}';
                $this->sendOutput(
                    $responseData,
                    array('Content-Type: application/json', 'HTTP/1.1 200 OK')
                );
            }else{
                $this->sendOutput(json_encode(array('mensagem' => 'E-mail e/ou senha incorretos')), 
                    array('Content-Type: application/json', 'HTTP/1.1 500 Internal Server Error')
                );
            }
           
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
            if($usuario->idUsuario > 0){
                $responseData = json_encode($usuario);
                $this->sendOutput(
                    $responseData,
                    array('Content-Type: application/json', 'HTTP/1.1 200 OK')
                );
            }else{
                $this->sendOutput(json_encode(array('mensagem' => 'Já existe um usuário com esse e-mail cadastrado')), 
                    array('Content-Type: application/json', 'HTTP/1.1 500 Internal Server Error')
                );
            }
           
        }
    }

    public function apagarDadosUsuarioAction(){
        $strErrorDesc = '';
        $requestMethod = $_SERVER["REQUEST_METHOD"];
        $arrQueryStringParams = $this->getQueryStringParams();
        
        if (strtoupper($requestMethod) == 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $usuarioModel = new UsuarioModel();
            $mangaModel = new MangaModel();
            $usuario = $usuarioModel->getUsuarioByEmail($data["email"]);
            $linhasDeletadas = 0;
            if($usuario){
                $mangaModel->deletarEmLoteByUsuario($usuario->idUsuario);
                $linhasDeletadas = $usuarioModel->deleteById($usuario->idUsuario);
            }
            $responseData = '{"linhasDeletadas": ' . $linhasDeletadas .'}';
            $this->sendOutput(
                $responseData,
                array('Content-Type: application/json', 'HTTP/1.1 200 OK')
            );
        }
    }
}
?>