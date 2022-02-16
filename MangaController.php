<?php
require "BaseController.php";
require "MangaModel.php";
require "Manga.php";
require "UsuarioModel.php";
require "Usuario.php";
require_once('class.smtp.php');
require_once('class.phpmailer.php');
include("Secret.php");
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

    public function alterarSenhaAction(){
        $strErrorDesc = '';
        $requestMethod = $_SERVER["REQUEST_METHOD"];
        $arrQueryStringParams = $this->getQueryStringParams();
        
        if (strtoupper($requestMethod) == 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $idUsuario = $data["idUsuario"];
            $senhaAtual = $data["senhaAtual"];
            $novaSenha = $data["novaSenha"];
            $usuarioModel = new UsuarioModel();
            $usuario = $usuarioModel->getUsuarioByIdAndSenha($idUsuario, $senhaAtual);
            if($usuario){
                $usuario->senha = $novaSenha;
                $usuarioModel->atualizarSenha($usuario->idUsuario, $usuario->senha);
                $responseData = '{"idUsuario": ' . $usuario->idUsuario .', "email": "' . $usuario->email.'"}';
                $this->sendOutput(
                    $responseData,
                    array('Content-Type: application/json', 'HTTP/1.1 200 OK')
                );
            }else{
                $this->sendOutput(json_encode(array('mensagem' => 'A senha atual está incorreta')), 
                    array('Content-Type: application/json', 'HTTP/1.1 500 Internal Server Error')
                );
            }
        }
    }

    public function redefinirSenhaAction(){
        $strErrorDesc = '';
        $requestMethod = $_SERVER["REQUEST_METHOD"];
        $arrQueryStringParams = $this->getQueryStringParams();
        
        if (strtoupper($requestMethod) == 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $email = $data["email"];
            $codigo = $data["codigo"];
            $senha = $data["senha"];
            $usuarioModel = new UsuarioModel();
            $usuario = $usuarioModel->getUsuarioByEmailAndCodigo($email, $codigo);
            if($usuario){
                $usuario->senha = $senha;
                $usuarioModel->atualizarSenha($usuario->idUsuario, $usuario->senha);
                $responseData = '{"idUsuario": ' . $usuario->id .', "email": "' . $usuario->email.'"}';
                $this->sendOutput(
                    $responseData,
                    array('Content-Type: application/json', 'HTTP/1.1 200 OK')
                );
            }else{
                $this->sendOutput(json_encode(array('mensagem' => 'E-mail e/ou código incorretos')), 
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

    public function enviarEmailRedefinirSenhaAction(){
        $strErrorDesc = '';
        $requestMethod = $_SERVER["REQUEST_METHOD"];
        $arrQueryStringParams = $this->getQueryStringParams();
        
        if (strtoupper($requestMethod) == 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $email = $data["email"];
            $codigo = rand(10000, 99999);
            $usuarioModel = new UsuarioModel();
            $usuario = $usuarioModel->getUsuarioByEmail($email);
            if($usuario){
                $usuarioModel->atualizarCodigo($usuario->idUsuario, $codigo);
                $result = $this->sendmail($email,'', '[Coleção de Mangás] Código de recuperação se senha ['.$codigo.']', 'O código de recuperação de senha é '.$codigo, 'altmess');
                if($result){
                    $usuario->senha = "";
                    $this->sendOutput(json_encode($usuario),
                        array('Content-Type: application/json', 'HTTP/1.1 200 OK')
                    );
                }else{
                    $this->sendOutput(json_encode(array('mensagem' => 'Ocorreu um erro no envio do email')), 
                    array('Content-Type: application/json', 'HTTP/1.1 500 Internal Server Error')
                    );
                }
            }else{
                $this->sendOutput(json_encode(array('mensagem' => 'Não foi encontrado um usuário com esse e-mail')), 
                array('Content-Type: application/json', 'HTTP/1.1 500 Internal Server Error')
                );
            }
        }
    }

    public function sendmail($to,$nameto,$subject,$message,$altmess)  {
        $from  = EMAIL;
        $namefrom = "Coleção de Mangás APP";
        $mail = new PHPMailer();  
        $mail->CharSet = 'UTF-8';
        $mail->isSMTP(true);   // by SMTP
        $mail->SMTPAuth   = true;   // user and password
        $mail->SMTPAutoTLS = true; 
        $mail->Port = 465 ; 

        $mail->Host       = "smtp.gmail.com";
        $mail->Username   = $from;  
        $mail->Password   = EMAIL_PASSWORD;
        $mail->SMTPSecure = 'ssl';    // options: 'ssl', 'tls' , ''  
        $mail->setFrom($from,$namefrom);   // From (origin)
        $mail->Subject  = $subject;
        $mail->AltBody  = $altmess;
        $mail->Body = $message;
        $mail->isHTML(false);   // Set HTML type
        $mail->addAddress($to, $nameto);
        //$mail->SMTPDebug = 2;
        return $mail->send();
      }
}
?>