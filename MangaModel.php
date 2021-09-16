<?php
require_once "Database.php";
 
class MangaModel extends Database
{
    public function listMangasByUsuario($idUsuario)
    {
        return $this->select("SELECT id_manga, id_usuario, chave, valor FROM manga where id_usuario = " . $idUsuario);
    }

    public function findByChave($chave)
    {
        return $this->select("SELECT id_manga, id_usuario, chave, valor FROM manga where chave = " . $chave)[0];
    }

    public function salvarOuAtualizar(Manga $manga){
        if($manga->novo == FALSE){
            date_default_timezone_set("America/Sao_Paulo");
            $sql = "UPDATE manga SET data_modificacao = '" .date("Y-m-d H:i:s") . "', valor = '" . $manga->valor . "' where chave = " . $manga->chave;
            $this->executeStatement($sql);
            return $this->findByChave($manga->chave);
        }else{
            $sql = "INSERT INTO manga (id_usuario, chave, valor)
                VALUES ('" . $manga->idUsuario . "', '" . $manga->chave . "', '" . $manga->valor . "')";
             $result = $this->executeStatement($sql);
            $manga->idManga = $result->insert_id;
            return $manga;
        }    
    }

    public function salvarEmLote($mangaLista){
        $queryOk = true;
        $linhasInseridas = 0;
        $this->connection->autocommit(FALSE);
        foreach($mangaLista as $manga){
            //echo(json_encode($manga->valor));
            $sql = "INSERT INTO manga (id_usuario, chave, valor) VALUES ('" . $manga->idUsuario . "', '" . $manga->chave . "', '" . $this->connection->real_escape_string(json_encode($manga->valor)) . "')";
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