<?php
declare(strict_types=1);

$promoters = $promoters ?? []; // Lista de usuários com a role 'promoter'
$clients = $clients ?? [];     // Lista de empresas com perfis ativos
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Painel Administrativo - Core RSVP</title>
    <link rel="stylesheet" href="https://wnebr.com/rsvp/public/assets/css/style.css">
</head>
<body>

<div class="app-container">
    <aside class="sidebar">
        <h2>Admin Core</h2>
        <nav style="display:flex; flex-direction:column; gap:0.5rem;">
            <a href="#" style="font-weight:600; text-decoration:none; color:var(--primary-color);">Visão Geral</a>
            <a href="#" style="text-decoration:none; color:var(--text-muted);">Configurações SMTP</a>
            <a href="#" style="text-decoration:none; color:var(--text-muted);">Integrações API</a>
        </nav>
    </aside>

    <main class="main-content">
        <header style="margin-bottom: 2rem;">
            <h1>Configurações Gerais do Sistema</h1>
            <p class="text-muted">Gerencie promotores, configure chaves de clientes corporativos e controle acessos.</p>
        </header>

        <div class="dashboard-grid" style="grid-template-columns: 1fr 1fr;">
            <div class="card">
                <div style="display:flex; justify-content:between; align-items:center; margin-bottom:1rem;">
                    <h3>Contas de Clientes Ativos</h3>
                    <button class="btn btn-primary" style="font-size:0.8rem; padding:0.25rem 0.5rem;">+ Novo Cliente</button>
                </div>
                <div class="table-responsive">
                    <table class="custom-table" style="font-size:0.85rem;">
                        <thead>
                            <tr>
                                <th>Empresa</th>
                                <th>Configuração SMTP</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Cliente Exemplo S/A</strong></td>
                                <td><span class="badge badge-confirmed">Customizado</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div style="display:flex; justify-content:between; align-items:center; margin-bottom:1rem;">
                    <h3>Promotores cadastrados</h3>
                    <button class="btn btn-primary" style="font-size:0.8rem; padding:0.25rem 0.5rem;">+ Novo Promotor</button>
                </div>
                <div class="table-responsive">
                    <table class="custom-table" style="font-size:0.85rem;">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>E-mail</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Promotor Operacional 01</td>
                                <td>operacao@wnebr.com</td>
                                <td><span class="badge badge-confirmed">Ativo</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

</body>
</html>