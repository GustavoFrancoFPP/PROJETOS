# Sistema de Loja TECHFIT - Documentação

## 📦 Arquivos Principais

### Produtos
- **produtos_loja.php** - Página principal da loja (USAR ESTE)
  - Busca produtos do banco de dados dinamicamente
  - Exibe apenas produtos ativos com estoque
  - Sistema de carrinho integrado
  
- ~~Produto.HTML~~ - Arquivo antigo com produtos fixos (NÃO USAR - mantido apenas como referência)

### Carrinho e Pagamento
- **carrinho.html** - Página do carrinho de compras
- **pagamento.php** - Finalização da compra
- **confirmacao.php** - Confirmação do pedido

### JavaScript
- **assets/js/produto.js** - Gerencia adição ao carrinho na página de produtos
- **assets/js/carrinho.js** - Classe Carrinho com todas as funcionalidades
- **assets/js/header-carrinho-simples.js** - Contador do carrinho no header
- **assets/js/pagamento.js** - Processamento do pagamento

## 🗄️ Estrutura do Banco de Dados

### Tabela: produtos
```sql
- id_produtos (INT, PK, AUTO_INCREMENT)
- nome_produto (VARCHAR(255))
- tipo_produto (VARCHAR(255))
- categoria (VARCHAR(255))
- preco (DECIMAL(7,2))
- quantidade (INT) - quantidade física
- quantidade_estoque (INT) - disponível para venda
- url_imagem (VARCHAR(500))
- descricao (TEXT)
- status (ENUM: 'ativo', 'inativo')
- created_at (TIMESTAMP)
```

### Tabela: venda
```sql
- id_venda (INT, PK, AUTO_INCREMENT)
- id_cliente (INT, FK)
- id_produtos (INT, FK)
- quantidade (INT)
- data_venda (DATETIME)
- valor_total (DECIMAL(10,2))
```

### Tabela: pedidos
```sql
- id_pedido (INT, PK, AUTO_INCREMENT)
- numero_pedido (VARCHAR(50))
- id_cliente (INT, FK)
- dados_cliente (JSON)
- itens (JSON)
- subtotal (DECIMAL(10,2))
- frete (DECIMAL(10,2))
- desconto (DECIMAL(10,2))
- total (DECIMAL(10,2))
- metodo_pagamento (VARCHAR(50))
- dados_pagamento (JSON)
- status (VARCHAR(50))
- data_pedido (DATETIME)
```

## 🛒 Fluxo de Compra

1. **Navegação de Produtos** (produtos_loja.php)
   - Cliente visualiza produtos disponíveis
   - Produtos buscados dinamicamente do banco
   - Botão "Adicionar ao carrinho"

2. **Carrinho** (carrinho.html)
   - Usa localStorage para persistir itens
   - Cliente pode alterar quantidades
   - Cálculo de subtotal, frete e desconto
   - Aplica cupons de desconto

3. **Pagamento** (pagamento.php)
   - Formulário de dados pessoais
   - Endereço de entrega
   - Escolha do método de pagamento
   - Grava pedido no banco

4. **Confirmação** (confirmacao.php)
   - Exibe número do pedido
   - Resume itens comprados
   - Status do pedido

## 🔧 Funcionalidades JavaScript

### Classe Carrinho (carrinho.js)
```javascript
- adicionarItem(produto)
- removerItem(id)
- alterarQuantidade(id, mudanca)
- aplicarCupom(codigo)
- calcularFrete(cep)
- finalizarCompra()
- salvarNoLocalStorage()
- atualizarContadorCarrinho()
```

### LocalStorage
```javascript
// Estrutura do carrinho
{
  carrinhoTechFit: [
    {
      id: "produto-1",
      nome: "Whey Protein",
      preco: 199.90,
      quantidade: 2,
      imagem: "...",
      descricao: "..."
    }
  ]
}
```

## 📝 Links Atualizados

Todos os links foram atualizados para usar `produtos_loja.php`:
- ✅ inicio.html
- ✅ pagina_1.html
- ✅ suporte.html
- ✅ unidade.html
- ✅ detalhes-unidade.html
- ✅ pagamento.html
- ✅ treinos.html
- ✅ carrinho.html

## 🎨 CSS
- **assets/css/produto.css** - Estilos da página de produtos
- **assets/css/carrinho.css** - Estilos do carrinho
- **assets/css/pagamento.css** - Estilos do pagamento

## 🚀 Como Usar

1. **Adicionar Produtos ao Banco**
   ```sql
   INSERT INTO produtos (nome_produto, tipo_produto, categoria, preco, 
                        quantidade_estoque, url_imagem, descricao, status) 
   VALUES ('Nome', 'Tipo', 'Categoria', 99.90, 50, 'url.jpg', 'Descrição', 'ativo');
   ```

2. **Acessar a Loja**
   - Navegue para: `http://localhost:8080/produtos_loja.php`
   - Produtos aparecem automaticamente do banco

3. **Testar Compra**
   - Adicione produtos ao carrinho
   - Acesse o carrinho clicando no ícone do header
   - Preencha dados de pagamento
   - Confirme o pedido

## 🔍 Verificar Pedidos

```sql
-- Ver todos os pedidos
SELECT * FROM pedidos ORDER BY data_pedido DESC;

-- Ver itens de um pedido específico
SELECT 
  p.numero_pedido,
  p.total,
  p.status,
  p.itens,
  p.dados_cliente
FROM pedidos p
WHERE p.numero_pedido = 'TECH-XXXXXXXX';
```

## ⚠️ Notas Importantes

1. **Produto.HTML é obsoleto** - Use apenas produtos_loja.php
2. **Estoque é gerenciado** - Verifique quantidade_estoque antes de vender
3. **localStorage** - Carrinho persiste entre sessões
4. **Imagens** - Coloque em `assets/images/imagens/`
5. **Status dos produtos** - Apenas produtos com status='ativo' aparecem na loja

## 🐛 Troubleshooting

**Produtos não aparecem?**
- Verifique se há produtos com status='ativo' no banco
- Verifique se quantidade_estoque > 0
- Veja logs de erro no console do navegador

**Carrinho não funciona?**
- Verifique se os scripts JS estão carregando
- Limpe localStorage: `localStorage.clear()`
- Verifique console do navegador (F12)

**Pagamento não grava?**
- Verifique conexão com banco de dados
- Veja logs em `error_log` do PHP
- Confirme que tabela `pedidos` existe
