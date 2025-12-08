// faturamento.js - Funcionalidades para relatórios de faturamento
document.addEventListener('DOMContentLoaded', function() {
    
    // Exportar PDF
    const exportBtn = document.querySelector('.btn-secondary');
    if (exportBtn) {
        exportBtn.addEventListener('click', function(e) {
            e.preventDefault();
            alert('Relatório exportado em PDF!');
        });
    }

    // Aplicar filtros
    const applyFilterBtn = document.querySelector('.action-btn');
    if (applyFilterBtn) {
        applyFilterBtn.addEventListener('click', function(e) {
            e.preventDefault();
            alert('Filtros de período aplicados!');
        });
    }

    // Simular gráfico interativo
    const bars = document.querySelectorAll('.main-content-grid .panel:first-child div > div');
    bars.forEach((bar, index) => {
        bar.addEventListener('mouseenter', function() {
            const value = this.querySelector('span:last-child').textContent;
            this.style.transform = 'scale(1.05)';
            this.title = `Faturamento: ${value}`;
        });
        
        bar.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    });

    // Detalhes da transação
    const transactionRows = document.querySelectorAll('.data-table tbody tr');
    transactionRows.forEach(row => {
        row.addEventListener('dblclick', function() {
            const id = this.cells[0].textContent;
            const cliente = this.cells[1].querySelector('strong').textContent;
            const valor = this.cells[3].textContent;
            const status = this.cells[5].textContent;
            
            alert(`Detalhes da Transação:\n\nID: ${id}\nCliente: ${cliente}\nValor: ${valor}\nStatus: ${status}`);
        });
    });

    console.log('✅ Módulo de faturamento carregado');
});