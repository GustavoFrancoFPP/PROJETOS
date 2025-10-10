document.addEventListener('DOMContentLoaded', () => {
  const tabs = document.querySelectorAll('.tab');
  const forms = document.querySelectorAll('.form');

  tabs.forEach(tab => {
    tab.addEventListener('click', () => {
      tabs.forEach(t => t.classList.remove('active'));
      forms.forEach(f => f.classList.remove('active'));

      tab.classList.add('active');
      document.getElementById(tab.dataset.tab + 'Form').classList.add('active');
    });
  });

  document.getElementById('loginForm').addEventListener('submit', e => {
    e.preventDefault();
    alert('Login efetuado com sucesso!');
  });

  document.getElementById('cadForm').addEventListener('submit', e => {
    e.preventDefault();
    alert('Cadastro realizado com sucesso!');
  });
});
