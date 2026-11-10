<footer class="site-footer">
  <div class="site-footer__in">
    <div class="site-footer__top">
      <div class="site-footer__brand">
        <img class="brand__logo" src="<?= e(asset('img/simbolo-admsc-sm.png')) ?>" width="160" height="160" alt="">
        <div>
          <strong>ADMSC</strong>
          <span><?= e($c['nome']) ?></span>
        </div>
      </div>
      <div class="site-footer__col">
        <strong>Morada</strong>
        <span><?= e($c['morada1']) ?><br><?= e($c['morada2']) ?></span>
      </div>
      <div class="site-footer__col">
        <strong>Contactos</strong>
        <a class="wrap" href="mailto:<?= e($c['email']) ?>"><?= e($c['email']) ?></a>
        <span>Telefone <?= e($c['telefone']) ?></span>
        <div class="social">
          <a href="<?= e($c['facebook']) ?>">Facebook</a>
          <a href="<?= e($c['instagram']) ?>">Instagram</a>
        </div>
      </div>
      <nav class="site-footer__nav" aria-label="Rodapé">
        <a href="/#modalidades">Modalidades</a>
        <a href="/noticias">Notícias</a>
        <a href="/#calendario">Calendário</a>
        <a href="/loja">Loja</a>
        <a href="/#faq">Perguntas frequentes</a>
      </nav>
    </div>
    <div class="site-footer__legal">
      <span>© <?= date('Y') ?> ADMSC — <?= e($c['nome']) ?></span>
      <div>
        <a href="/privacidade">Política de privacidade</a>
        <a href="/cookies">Cookies</a>
        <a href="/termos">Termos e condições</a>
        <a href="/trocas-e-devolucoes">Trocas e devoluções</a>
        <a href="https://www.livroreclamacoes.pt">Livro de Reclamações</a>
      </div>
    </div>
  </div>
</footer>
