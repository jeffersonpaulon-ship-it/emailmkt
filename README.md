# RSVP Manager

Sistema de RSVP com envio e acompanhamento de e-mails (aberturas/cliques), páginas
públicas de confirmação de presença e captura de leads, e estrutura preparada para
WhatsApp e ligações. Três papéis de acesso:

- **Admin**: cadastra promotores e clientes, define quantas mensagens por hora cada
  promotor pode disparar.
- **Promotor**: cria eventos, configura páginas públicas (confirmação/captura),
  cadastra contatos, cria templates e campanhas de e-mail, acompanha aberturas e
  cliques, registra mensagens de WhatsApp e ligações, e decide o que cada cliente
  pode ver.
- **Cliente**: acessa um painel somente leitura, restrito ao que o promotor liberou
  para cada evento.

Stack: PHP 8.2+ puro (sem framework), MySQL/MariaDB, sem dependências via Composer.

## Requisitos

- PHP 8.2+ com extensões `pdo_mysql`
- MySQL ou MariaDB
- Um servidor SMTP (qualquer provedor com usuário/senha, ou SMTP interno)

## Configuração

1. Copie `.env.example` para `.env` e preencha as credenciais de banco e SMTP.
2. Crie o banco e aplique o schema:
   ```
   mysql -u seu_usuario -p seu_banco < database/schema.sql
   ```
3. Crie o usuário administrador inicial:
   ```
   php database/seed.php admin@example.com "senha-forte"
   ```
4. Aponte o document root do seu servidor web para a pasta `public/` (há um
   `.htaccess` na raiz que redireciona automaticamente caso o vhost aponte para a
   raiz do repositório).

Para desenvolvimento local, o servidor embutido do PHP funciona:
```
php -S 127.0.0.1:8000 -t public
```

## Envio de e-mails (fila com limite por hora)

Os e-mails de campanha não são enviados na hora — ficam na fila
(`campaign_recipients`) e são processados pelo worker `bin/send_queue.php`, que
respeita o limite de mensagens/hora configurado pelo admin para cada promotor.
Configure um cron para rodá-lo periodicamente (ex: a cada minuto):

```
* * * * * php /caminho/para/o/projeto/bin/send_queue.php >> /caminho/para/o/projeto/storage/logs/send_queue.log 2>&1
```

O tracking de abertura (pixel 1x1) e de cliques (redirecionamento) é embutido
automaticamente em cada e-mail enviado — não requer nenhum serviço externo.

## WhatsApp e ligações

A estrutura de dados e as telas para WhatsApp (`whatsapp_messages`) e ligações
(`calls`) já estão prontas, mas **sem integração real com nenhum provedor** nesta
etapa — os envios ficam registrados como "simulados"/manuais para permitir plugar
uma API (ex: WhatsApp Business API, Twilio) depois sem redesenhar o fluxo.

## Estrutura

```
public/            front controller + assets
src/Controllers/   controllers HTTP
src/Models/        acesso a dados (PDO)
src/Mailer/        cliente SMTP e renderização de e-mail com tracking
views/             templates PHP
database/schema.sql  DDL completo
database/seed.php    cria o admin inicial
bin/send_queue.php    worker de envio (rodar via cron)
```
