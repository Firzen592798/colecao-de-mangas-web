<?php
    class Usuario{
        public $idUsuario;
        public $email;
        public $senha;
        function __construct($email= null, $senha = null) {
            $this->email = $email;
            $this->senha = $senha;
        }
    }
?>