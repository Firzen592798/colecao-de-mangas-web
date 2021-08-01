<?php
require_once "Database.php";
 
class MangaModel extends Database
{
    public function getMangas()
    {
        return $this->select("SELECT * FROM manga");
    }

    public function salvarOuAtualizar(Manga $manga){
        if($manga->novo == FALSE){
            $sql = "UPDATE manga SET valor = '" . $manga->valor . "' where chave = " . $manga->chave;
        }else{
            $sql = "INSERT INTO manga (id_usuario, chave, valor)
                VALUES ('" . $manga->idUsuario . "', '" . $manga->chave . "', '" . $manga->valor . "')";
        }
        return $this->executeStatement($sql);
    }
}
?>