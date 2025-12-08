// treinos.js - Sistema de Gestão de Treinos
class TreinosManager {
    constructor() {
        this.treinos = JSON.parse(localStorage.getItem('techfit_treinos')) || [];
        this.clientes = JSON.parse(localStorage.getItem('techfit_clientes')) || this.criarClientesDemo();
        this.funcionarios = JSON.parse(localStorage.getItem('techfit_funcionarios')) || this.criarFuncionariosDemo();
        this.filtros = {
            status: 'all',
            objetivo: 'all',
            instrutor: 'all',
            search: ''
        };
        this.init();
    }

    init() {
        this.carregarDadosSelects();
        this.renderizarTreinos();
        this.configurarEventos();
        this.atualizarEstatisticas();
        console.log('🏋️ Sistema de Treinos inicializado');
    }

    criarClientesDemo() {
        const clientesDemo = [
            { id: 1, nome: 'João Silva', email: 'joao@email.com', telefone: '(11) 99999-9999' },
            { id: 2, nome: 'Maria Santos', email: 'maria@email.com', telefone: '(11) 88888-8888' },
            { id: 3, nome: 'Pedro Oliveira', email: 'pedro@email.com', telefone: '(11) 77777-7777' },
            { id: 4, nome: 'Ana Costa', email: 'ana@email.com', telefone: '(11) 66666-6666' }
        ];
        localStorage.setItem('techfit_clientes', JSON.stringify(clientesDemo));
        return clientesDemo;
    }

    criarFuncionariosDemo() {
        const funcionariosDemo = [
            { id: 1, nome: 'Carlos Personal', especialidade: 'Musculação' },
            { id: 2, nome: 'Fernanda Coach', especialidade: 'CrossFit' },
            { id: 3, nome: 'Ricardo Trainer', especialidade: 'Pilates' }
        ];
        localStorage.setItem('techfit_funcionarios', JSON.stringify(funcionariosDemo));
        return funcionariosDemo;
    }

    carregarDadosSelects() {
        this.carregarClientesSelect();
        this.carregarFuncionariosSelect();
        this.carregarFiltroInstrutores();
    }

    carregarClientesSelect() {
        const select = document.getElementById('clienteSelect');
        if (!select) return;

        select.innerHTML = '<option value="">Selecione um cliente</option>';
        this.clientes.forEach(cliente => {
            const option = document.createElement('option');
            option.value = cliente.id;
            option.textContent = cliente.nome;
            select.appendChild(option);
        });
    }

    carregarFuncionariosSelect() {
        const select = document.getElementById('instrutorSelect');
        if (!select) return;

        select.innerHTML = '<option value="">Selecione um instrutor</option>';
        this.funcionarios.forEach(funcionario => {
            const option = document.createElement('option');
            option.value = funcionario.id;
            option.textContent = funcionario.nome;
            select.appendChild(option);
        });
    }

    carregarFiltroInstrutores() {
        const select = document.getElementById('filter-instrutor');
        if (!select) return;

        select.innerHTML = '<option value="all">Todos</option>';
        this.funcionarios.forEach(funcionario => {
            const option = document.createElement('option');
            option.value = funcionario.id;
            option.textContent = funcionario.nome;
            select.appendChild(option);
        });
    }

    configurarEventos() {
        // Filtros
        document.getElementById('filter-status').addEventListener('change', (e) => {
            this.filtros.status = e.target.value;
            this.renderizarTreinos();
        });

        document.getElementById('filter-objetivo').addEventListener('change', (e) => {
            this.filtros.objetivo = e.target.value;
            this.renderizarTreinos();
        });

        document.getElementById('filter-instrutor').addEventListener('change', (e) => {
            this.filtros.instrutor = e.target.value;
            this.renderizarTreinos();
        });

        document.getElementById('searchInput').addEventListener('input', (e) => {
            this.filtros.search = e.target.value.toLowerCase();
            this.renderizarTreinos();
        });

        document.getElementById('btnClearFilters').addEventListener('click', () => {
            this.limparFiltros();
        });

        // Botões de ação
        document.getElementById('btnNovoTreino').addEventListener('click', () => {
            this.abrirModalNovoTreino();
        });

        document.getElementById('btnEmptyNovoTreino').addEventListener('click', () => {
            this.abrirModalNovoTreino();
        });

        // Modal
        document.getElementById('modalClose').addEventListener('click', () => {
            this.fecharModal();
        });

        document.getElementById('btnCancelar').addEventListener('click', () => {
            this.fecharModal();
        });

        document.getElementById('treinoForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.salvarTreino();
        });

        // Fechar modal ao clicar fora
        document.getElementById('treinoModal').addEventListener('click', (e) => {
            if (e.target.id === 'treinoModal') {
                this.fecharModal();
            }
        });
    }

    limparFiltros() {
        this.filtros = {
            status: 'all',
            objetivo: 'all',
            instrutor: 'all',
            search: ''
        };

        document.getElementById('filter-status').value = 'all';
        document.getElementById('filter-objetivo').value = 'all';
        document.getElementById('filter-instrutor').value = 'all';
        document.getElementById('searchInput').value = '';

        this.renderizarTreinos();
    }

    renderizarTreinos() {
        const grid = document.getElementById('treinosGrid');
        const emptyState = document.getElementById('emptyState');

        if (!grid) return;

        const treinosFiltrados = this.filtrarTreinos();

        if (treinosFiltrados.length === 0) {
            grid.style.display = 'none';
            emptyState.style.display = 'block';
        } else {
            grid.style.display = 'grid';
            emptyState.style.display = 'none';

            grid.innerHTML = treinosFiltrados.map(treino => this.criarCardTreino(treino)).join('');
            
            // Adicionar event listeners aos botões
            this.configurarEventosCards();
        }

        this.atualizarEstatisticas();
    }

    filtrarTreinos() {
        return this.treinos.filter(treino => {
            // Filtro de status
            if (this.filtros.status !== 'all' && treino.status !== this.filtros.status) {
                return false;
            }

            // Filtro de objetivo
            if (this.filtros.objetivo !== 'all' && treino.objetivo !== this.filtros.objetivo) {
                return false;
            }

            // Filtro de instrutor
            if (this.filtros.instrutor !== 'all' && treino.id_funcionario != this.filtros.instrutor) {
                return false;
            }

            // Filtro de busca
            if (this.filtros.search) {
                const searchTerm = this.filtros.search;
                const cliente = this.clientes.find(c => c.id === treino.id_cliente);
                const treinoMatch = treino.nome_treino.toLowerCase().includes(searchTerm);
                const clienteMatch = cliente && cliente.nome.toLowerCase().includes(searchTerm);
                
                if (!treinoMatch && !clienteMatch) {
                    return false;
                }
            }

            return true;
        });
    }

    criarCardTreino(treino) {
        const cliente = this.clientes.find(c => c.id === treino.id_cliente);
        const funcionario = this.funcionarios.find(f => f.id === treino.id_funcionario);

        const formatarData = (data) => {
            return new Date(data).toLocaleDateString('pt-BR');
        };

        return `
            <div class="treino-card" data-id="${treino.id_treino}">
                <div class="treino-header">
                    <h3 class="treino-title">${treino.nome_treino}</h3>
                    <span class="treino-status status-${treino.status}">${treino.status}</span>
                </div>
                
                <div class="treino-info">
                    <div class="info-item">
                        <i class="fas fa-user"></i>
                        <span><strong>Cliente:</strong> ${cliente ? cliente.nome : 'N/A'}</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <span><strong>Instrutor:</strong> ${funcionario ? funcionario.nome : 'N/A'}</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-bullseye"></i>
                        <span><strong>Objetivo:</strong> ${this.formatarObjetivo(treino.objetivo)}</span>
                    </div>
                    ${treino.duracao ? `
                    <div class="info-item">
                        <i class="fas fa-clock"></i>
                        <span><strong>Duração:</strong> ${treino.duracao} min</span>
                    </div>
                    ` : ''}
                </div>
                
                <div class="treino-dates">
                    <div class="date-item">
                        <span class="date-label">Início</span>
                        <span class="date-value">${formatarData(treino.data_inicio)}</span>
                    </div>
                    ${treino.data_fim ? `
                    <div class="date-item">
                        <span class="date-label">Término</span>
                        <span class="date-value">${formatarData(treino.data_fim)}</span>
                    </div>
                    ` : ''}
                </div>
                
                <div class="treino-actions">
                    <button class="btn-secondary btn-editar" data-id="${treino.id_treino}">
                        <i class="fas fa-edit"></i>
                        Editar
                    </button>
                    <button class="btn-danger btn-excluir" data-id="${treino.id_treino}">
                        <i class="fas fa-trash"></i>
                        Excluir
                    </button>
                </div>
            </div>
        `;
    }

    formatarObjetivo(objetivo) {
        const objetivos = {
            'emagrecimento': 'Emagrecimento',
            'hipertrofia': 'Hipertrofia',
            'condicionamento': 'Condicionamento',
            'reabilitacao': 'Reabilitação',
            'outro': 'Outro'
        };
        return objetivos[objetivo] || objetivo;
    }

    configurarEventosCards() {
        // Botões editar
        document.querySelectorAll('.btn-editar').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = e.target.closest('.btn-editar').dataset.id;
                this.editarTreino(parseInt(id));
            });
        });

        // Botões excluir
        document.querySelectorAll('.btn-excluir').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = e.target.closest('.btn-excluir').dataset.id;
                this.excluirTreino(parseInt(id));
            });
        });
    }

    abrirModalNovoTreino() {
        document.getElementById('modalTitle').textContent = 'Novo Treino';
        document.getElementById('treinoForm').reset();
        document.getElementById('treinoId').value = '';
        document.getElementById('statusSelect').value = 'ativo';
        document.getElementById('dataInicio').value = new Date().toISOString().split('T')[0];
        
        this.abrirModal();
    }

    editarTreino(id) {
        const treino = this.treinos.find(t => t.id_treino === id);
        if (!treino) return;

        document.getElementById('modalTitle').textContent = 'Editar Treino';
        document.getElementById('treinoId').value = treino.id_treino;
        document.getElementById('clienteSelect').value = treino.id_cliente;
        document.getElementById('instrutorSelect').value = treino.id_funcionario;
        document.getElementById('nomeTreino').value = treino.nome_treino;
        document.getElementById('objetivoSelect').value = treino.objetivo;
        document.getElementById('duracao').value = treino.duracao || '';
        document.getElementById('statusSelect').value = treino.status;
        document.getElementById('dataInicio').value = treino.data_inicio;
        document.getElementById('dataFim').value = treino.data_fim || '';
        document.getElementById('observacoes').value = treino.observacoes || '';

        this.abrirModal();
    }

    abrirModal() {
        document.getElementById('treinoModal').style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    fecharModal() {
        document.getElementById('treinoModal').style.display = 'none';
        document.body.style.overflow = '';
    }

    salvarTreino() {
        const formData = new FormData(document.getElementById('treinoForm'));
        const id = document.getElementById('treinoId').value;
        const isEdit = !!id;

        const treino = {
            id_treino: isEdit ? parseInt(id) : this.gerarNovoId(),
            id_cliente: parseInt(formData.get('clienteSelect')),
            id_funcionario: parseInt(formData.get('instrutorSelect')),
            nome_treino: formData.get('nomeTreino'),
            objetivo: formData.get('objetivoSelect'),
            duracao: formData.get('duracao') ? parseInt(formData.get('duracao')) : null,
            status: formData.get('statusSelect'),
            data_inicio: formData.get('dataInicio'),
            data_fim: formData.get('dataFim') || null,
            observacoes: formData.get('observacoes'),
            updated_at: new Date().toISOString()
        };

        if (!isEdit) {
            treino.created_at = new Date().toISOString();
            this.treinos.push(treino);
        } else {
            const index = this.treinos.findIndex(t => t.id_treino === treino.id_treino);
            if (index !== -1) {
                this.treinos[index] = { ...this.treinos[index], ...treino };
            }
        }

        this.salvarNoLocalStorage();
        this.renderizarTreinos();
        this.fecharModal();

        this.mostrarNotificacao(
            isEdit ? 'Treino atualizado com sucesso!' : 'Treino criado com sucesso!',
            'success'
        );
    }

    excluirTreino(id) {
        if (confirm('Tem certeza que deseja excluir este treino?')) {
            this.treinos = this.treinos.filter(t => t.id_treino !== id);
            this.salvarNoLocalStorage();
            this.renderizarTreinos();
            
            this.mostrarNotificacao('Treino excluído com sucesso!', 'success');
        }
    }

    gerarNovoId() {
        const ids = this.treinos.map(t => t.id_treino);
        return ids.length > 0 ? Math.max(...ids) + 1 : 1;
    }

    salvarNoLocalStorage() {
        localStorage.setItem('techfit_treinos', JSON.stringify(this.treinos));
    }

    atualizarEstatisticas() {
        const total = this.treinos.length;
        const ativos = this.treinos.filter(t => t.status === 'ativo').length;
        const concluidos = this.treinos.filter(t => t.status === 'concluido').length;

        document.getElementById('totalTreinos').textContent = total;
        document.getElementById('treinosAtivos').textContent = ativos;
        document.getElementById('treinosConcluidos').textContent = concluidos;
    }

    mostrarNotificacao(mensagem, tipo = 'success') {
        const notification = document.createElement('div');
        notification.className = `notification ${tipo}`;
        notification.innerHTML = `
            <i class="fas fa-${tipo === 'success' ? 'check' : 'exclamation'}"></i>
            <span>${mensagem}</span>
        `;

        notification.style.cssText = `
            position: fixed;
            top: 100px;
            right: 20px;
            background: ${tipo === 'success' ? '#4CAF50' : '#f44336'};
            color: white;
            padding: 15px 20px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
            z-index: 10000;
            animation: slideInRight 0.3s ease;
            max-width: 300px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        `;

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
}

// Inicializar quando o DOM estiver carregado
document.addEventListener('DOMContentLoaded', function() {
    new TreinosManager();
});

// Adicionar estilos para notificações
const notificationStyles = document.createElement('style');
notificationStyles.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(notificationStyles);