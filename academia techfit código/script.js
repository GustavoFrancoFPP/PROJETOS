document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.classes button').forEach(btn => {
        btn.addEventListener('click', function () {
            const li   = this.closest('li');
            const nome = li.querySelector('.name').textContent;
            const hora = li.querySelector('.time').textContent;
            const spot = li.querySelector('.spots');

            if (!spot) return;

            let vagas = parseInt(spot.textContent.match(/\d+/)[0]);
            if (vagas === 0) return;

            vagas--;
            spot.textContent = `${vagas} vagas disponíveis`;

            if (vagas === 0) {
                this.replaceWith(createFullBadge());
            }
            alert(`Aula "${nome}" agendada para ${hora} com sucesso!`);
        });
    });
    document.querySelectorAll('.weekdays div').forEach(d => {
        d.addEventListener('click', function () {
            document.querySelector('.weekdays .active')?.classList.remove('active');
            this.classList.add('active');
        });
    });

    function createFullBadge() {
        const span = document.createElement('span');
        span.className = 'full';
        span.textContent = 'LOTADO';
        return span;
    }
});