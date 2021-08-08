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

    public function salvarEmLote($mangaLista){
        $queryOk = true;
        $linhasInseridas = 0;
        $this->connection->autocommit(FALSE);
        foreach($mangaLista as $manga){
            //echo(json_encode($manga->valor));
            $sql = "INSERT INTO manga (id_usuario, chave, valor) VALUES ('" . $manga->idUsuario . "', '" . $manga->chave . "', '" . json_encode($manga->valor) . "')";
            //$this->executeStatement($sql);
            $result = $this->connection->query($sql);
            if(!$result){
                $queryOk = false;
                $linhasInseridas = 0;
                break;
            }else{
                $linhasInseridas++;
            }
        }
        if($queryOk){
            $this->connection->commit();
        }
        return $linhasInseridas;
    }

    public function deletarEmLoteByUsuario($usuarioId){
        $queryOk = true;
        $sql = "delete from manga where id_usuario = ".$usuarioId;
        $result = $this->executeStatement($sql);
        return $result->affected_rows;
    }
}
?>