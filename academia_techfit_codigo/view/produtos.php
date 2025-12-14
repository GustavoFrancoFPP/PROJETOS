<?php
// Salve dentro da pasta: modelo/

class Produto {
    private $id;
    private $nome;
    private $preco;
    private $categoria;
    private $imagem;

    public function __construct($id, $nome, $preco, $categoria, $imagem) {
        $this->id = $id;
        $this->nome = $nome;
        $this->preco = $preco;
        $this->categoria = $categoria;
        $this->imagem = $imagem;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getNome() { return $this->nome; }
    public function getPreco() { return $this->preco; }
    public function getCategoria() { return $this->categoria; }
    public function getImagem() { return $this->imagem; }

    // Setters
    public function setId($id) { $this->id = $id; }
    public function setNome($nome) { $this->nome = $nome; }
    public function setPreco($preco) { $this->preco = $preco; }
    public function setCategoria($categoria) { $this->categoria = $categoria; }
    public function setImagem($imagem) { $this->imagem = $imagem; }
}
?>