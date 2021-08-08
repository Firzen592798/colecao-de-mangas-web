<?php
    class Manga{
        public $idManga;
        public $novo;
        public $idUsuario;
        public $chave;
        public $valor;

        function __construct($idUsuario, $chave, $valor) {
            $this->idUsuario = $idUsuario;
            $this->chave = $chave;
            $this->valor = $valor;
        }
    }
?>