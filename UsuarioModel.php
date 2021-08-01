<?php
require_once "Database.php";
 
class UsuarioModel extends Database
{
    public function login($email, $senha)
    {
        return $this->select("SELECT * FROM usuario where email = '" . $email . "' and senha = '" . $senha);
    }

    public function salvarOuAtualizar(Usuario $usuario){
        $sql = "INSERT INTO usuario (email, senha)
        VALUES ('" . $usuario->email . "', '" . $usuario->senha. "')";
        $result =  $this->executeStatement($sql);
        $usuario->idUsuario = $result->insert_id;
        return $usuario;
    }
}
?>