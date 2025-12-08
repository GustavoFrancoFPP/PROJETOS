<?php

class Usuario {
    private $nomeUsuario;
    private $senhaUsuario;
    private $tipoUsuario;
    private $idCliente;
    private $idFuncionario;

    public function __construct($nomeUsuario, $senhaUsuario, $tipoUsuario = 'cliente', $idCliente = null, $idFuncionario = null) {
        $this->nomeUsuario = $nomeUsuario;
        $this->senhaUsuario = $senhaUsuario;
        $this->tipoUsuario = $tipoUsuario;
        $this->idCliente = $idCliente;
        $this->idFuncionario = $idFuncionario;
    }

    // Getters
    public function getNomeUsuario() { return $this->nomeUsuario; }
    public function getSenhaUsuario() { return $this->senhaUsuario; }
    public function getTipoUsuario() { return $this->tipoUsuario; }
    public function getIdCliente() { return $this->idCliente; }
    public function getIdFuncionario() { return $this->idFuncionario; }

    // Setters
    public function setSenhaUsuario($senhaUsuario) { $this->senhaUsuario = $senhaUsuario; }
    public function setTipoUsuario($tipoUsuario) { $this->tipoUsuario = $tipoUsuario; }
}