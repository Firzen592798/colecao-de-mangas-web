<?php
require_once "Database.php";
 
class UsuarioModel extends Database
{
    public function login($email, $senha)
    {
        return $this->select("SELECT * FROM usuario where email = '" . $email ."' and senha = '" . $senha ."'");
    }

    public function salvarOuAtualizar(Usuario $usuario){
        $sql = "INSERT INTO usuario (email, senha) VALUES ('" . $usuario->email . "', '" . $usuario->senha. "')";
        $result =  $this->executeStatement($sql);
        $usuario->idUsuario = $result->insert_id;
        return $usuario;
    }

    public function getUsuarioByEmail($email)
    {
        $data = $this->select("SELECT * FROM usuario where email = ? limit 1", ["s", $email]);
        if($data){
            $usuarioItem = $data[0];
            $usuario = new Usuario($email, null);
            $usuario->idUsuario=$usuarioItem["id_usuario"];
        }else{
            $usuario = null;
        }
        return $usuario;
    }

    public function deleteById($idUsuario){
        $data = $this->executeStatement("delete FROM usuario where id_usuario = ?", ["i", $idUsuario]);
        return $data->affected_rows;
    }
}
?>