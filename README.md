# ADMSC

Site e loja da Associação Desportiva Marcial Samora Correia. PHP 8 puro, MySQL/MariaDB (PDO), HTML/CSS/JS sem frameworks. Alojamento: Hostinger (Apache).

## Estrutura

- `public_html/` — raiz pública (front controller `index.php`, `assets/`, `uploads/`)
- `app/` — código fora da raiz pública (rotas, vistas, conteúdos, base de dados)
- `sql/schema.sql` — estrutura da base de dados

## Ambiente local (XAMPP)

1. Instalar o XAMPP (PHP 8.2+) e ligar **Apache** e **MySQL** no XAMPP Control Panel.
2. Acrescentar a `C:\xampp\apache\conf\extra\httpd-vhosts.conf`:

   ```apache
   Listen 8080
   <VirtualHost *:8080>
       ServerName localhost
       DocumentRoot "C:/caminho/para/ADMSC/public_html"
       <Directory "C:/caminho/para/ADMSC/public_html">
           Options -Indexes +FollowSymLinks
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```

   e reiniciar o Apache. O site fica em http://localhost:8080.
3. Criar a base de dados `admsc` (utf8mb4) e um utilizador só com acesso a ela, e carregar `sql/schema.sql`.
4. Copiar `app/config.example.php` para `app/config.php` e preencher as credenciais. Este ficheiro não vai para o Git.
5. No `php.ini` do XAMPP, ativar `extension=gd` (as fotos carregadas são reduzidas e perdem os metadados, como a localização GPS) e reiniciar o Apache.
6. Criar a conta da direção: `C:\xampp\php\php.exe app/cli/create-admin.php` (pede email, nome e password; a password não aparece no ecrã). Correr outra vez com o mesmo email serve para mudar a password.

## Painel de administração

Em `/admin` (login em `/admin/login`). Tem:

- **Notícias:** título, descrição e fotos. A data é o momento em que se publica. Podem ficar em rascunho.
- **Eventos:** data, horas, local e descrição. Vão alimentar o calendário.
- **Loja:** produtos com preço, promoção (com datas opcionais), tamanhos com stock, fotos e opção de ficar oculto.
- **Conta:** mudar a password (termina as sessões noutros dispositivos).

Segurança: sessões com cookie HttpOnly/SameSite=Lax (Secure em HTTPS), sessão renovada no login e termina após 2 h sem atividade, CSRF em todos os POST, limite de 5 tentativas falhadas por email e 20 por IP em 15 minutos, fotos validadas pelo conteúdo e gravadas com nome aleatório em `public_html/uploads/` (onde o PHP não executa).

## Decisões

- **Contas:** têm conta a direção (painel de administração) e, opcionalmente, os clientes da loja. Também se pode comprar sem conta. (Alteração ao handoff, que previa só a conta da direção na v1.)
- **Passwords:** só se guarda o `password_hash()`. Os tokens de reposição/confirmação guardam-se como SHA-256.
- **Datas:** a base de dados trabalha em UTC; a apresentação converte para Europe/Lisbon.
