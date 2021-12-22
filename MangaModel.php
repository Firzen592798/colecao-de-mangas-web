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
            $sql = "INSERT INTO manga (id_usuario, chave, valor) VALUES ('" . $manga->idUsuario . "', '" . $manga->chave . "', '" . $this->connection->real_escape_string(json_encode($manga->valor)) . "')";
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

    //Recebe uma lista de mangás que estão no aplicativo mas ainda não estão sincronizados no banco. Faz o devido tratamento para fazer ou insert ou update
    public function sincronizarNaEntrada($mangaLista){
        //echo(json_encode($mangaLista));
        $queryOk = true;
        $linhasAfetadas = 0;
        $this->connection->autocommit(FALSE);
        $chaves = array_map(function ($v){
            return $v->chave;
        },$mangaLista);
        $chavesStr = implode(', ', $chaves);
        //Procura os mangás no banco que correspondem às entradas passadas por parâmetro
        $sql = "select distinct chave from manga where chave in(".$chavesStr.")";
        //echo($sql);
        $resultBanco = $this->select($sql);
        foreach($mangaLista as $itemManga){ 
            //Procura pelos mangás que já existem no banco de dados para decidir se fará o insert ou o update
            $update = false;
            foreach($resultBanco as $linha){
                $chaveResultBanco =  $linha["chave"];
                if($chaveResultBanco == $itemManga->chave){ // achou no banco -> fará o update e não o insert
                    $update = true;
                }
            }
            if($update){
                date_default_timezone_set("America/Sao_Paulo");
                $sql = "UPDATE manga SET data_modificacao = '" .date("Y-m-d H:i:s") . "', valor = '" . $this->connection->real_escape_string(json_encode($itemManga->valor)) . "' where chave = " . $itemManga->chave;
            }else{
                $sql = "INSERT INTO manga (id_usuario, chave, valor) VALUES ('" . $itemManga->idUsuario . "', '" . $itemManga->chave . "', '" . $this->connection->real_escape_string(json_encode($itemManga->valor)) . "')";
            }
            //echo($sql);
            $resultInsertUpdateQuery = $this->connection->query($sql);
            if(!$resultInsertUpdateQuery){
                $queryOk = false;
                $linhasAfetadas = 0;
                break;
            }else{
                $linhasAfetadas++;
            }
        }
        if($queryOk){
            $this->connection->commit();
        }
        return $linhasAfetadas;
    }

    public function deletarEmLoteByUsuario($usuarioId){
        $queryOk = true;
        $sql = "delete from manga where id_usuario = ".$usuarioId;
        $result = $this->executeStatement($sql);
        return $result->affected_rows;
    }

    public function deletarByUsuarioIdAndKey($usuarioId, $mangaKey){
        $sql = "delete from manga where chave = '".$mangaKey . "' and id_usuario = ".$usuarioId;
        $result = $this->executeStatement($sql);
        return $result->affected_rows;
    }
}
?>