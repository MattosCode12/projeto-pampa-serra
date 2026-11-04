# Projeto sobre Ferrorama com IOT

<p align="center">
<img width="300" height="300" alt="pmp5" src="https://github.com/user-attachments/assets/bcf043f7-7b6f-4393-8cd0-3c01835d52de" />
</p>

---

<p align="center">
   <img src="http://img.shields.io/static/v1?label=STATUS&message=EM%20DESENVOLVIMENTO&color=RED&style=for-the-badge" #vitrinedev/>
</p>

<p align="center">
   <img src="https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP"/>
   <img src="https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL"/>
   <img src="https://img.shields.io/badge/Bootstrap-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white" alt="Bootstrap"/>
   <img src="https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black" alt="JavaScript"/>
   <img src="https://img.shields.io/badge/LICENSE-MIT-green?style=for-the-badge" alt="Licença MIT"/>
</p>

Projeto desenvolvido com foco em tecnologia, automação e monitoramento ferroviário, utilizando conceitos de **IoT (Internet of Things)**.

## Índice

- [Descrição do projeto](#descrição-do-projeto)
- [Funcionalidades](#funcionalidades)
- [Funcionalidades adicionais](#funcionalidades-adicionais)
- [Tecnologias utilizadas](#tecnologias-utilizadas)
- [Estrutura do projeto](#estrutura-do-projeto)
- [Banco de dados](#banco-de-dados)
- [Como executar o projeto](#como-executar-o-projeto)
- [Segurança](#segurança)
- [Documentação](#documentação)
- [Metodologia de desenvolvimento](#metodologia-de-desenvolvimento)
- [Próximos passos](#próximos-passos)
- [Licença](#licença)

## Descrição do projeto

O **Pampa-Serra** é um projeto desenvolvido com foco em tecnologia, automação e monitoramento ferroviário, utilizando conceitos de IoT (Internet of Things). O objetivo principal é criar uma solução inteligente para acompanhamento de informações da ferrovia em tempo real, integrando sensores, conectividade e uma interface moderna para visualização dos dados.

O sistema busca melhorar o controle e a segurança ferroviária por meio da coleta e transmissão de dados importantes, permitindo um gerenciamento mais eficiente das operações.

A linha monitorada vai de **Porto Alegre (KM 0) até Gramado (KM 120)**, passando por Aeroporto, São Leopoldo, Novo Hamburgo, Sapiranga, Parobé, Taquara, Igrejinha, Três Coroas e Canela, com um sensor instalado em cada ponto do trajeto.

## Funcionalidades

| RF | Descrição |
|----|-----------|
| RF01 | Permitir login de usuários no sistema |
| RF02 | Validar credenciais de acesso no banco de dados |
| RF03 | Permitir cadastro de sensores ferroviários |
| RF04 | Listar sensores cadastrados no sistema |
| RF05 | Permitir exclusão de sensores cadastrados |
| RF06 | Exibir dados ferroviários em tempo real |
| RF07 | Mostrar a localização atual do trem |
| RF08 | Classificar automaticamente o status operacional do trem |
| RF09 | Gerar relatórios analíticos do sistema ferroviário |
| RF10 | Permitir visualização de relatórios |
| RF11 | Permitir logout do usuário |
| RF12 | Atualizar dados automaticamente em tempo real |
| RF13 | Exibir localização do trem através de mapa interativo |
| RF14 | Classificar automaticamente situações de: Normal, Alerta e Falha |
| RF15 | Permitir visualização de relatórios anteriores |
| RF16 | Permitir visualização detalhada dos sensores cadastrados |


> Para acompanhar o status atual de cada Requisito Funcional (concluído, em desenvolvimento ou pendente), consulte o [Kanban do projeto no GitHub Projects](#).

## Funcionalidades adicionais

Além dos requisitos funcionais acima, o sistema também conta com:

| Módulo | Descrição |
|--------|-----------|
| Trens | Cadastro, edição e visualização detalhada dos trens, com código, modelo e status (Normal, Alerta ou Falha) |
| Rotas | Cadastro e edição de rotas com origem, destino, parada, faixa de velocidade e faixa de consumo esperadas |
| Usuários | Cadastro, edição e exclusão de usuários, com perfis de **administrador** e **usuário comum** |
| Incidentes | Registro de incidentes ferroviários classificados por tipo e gravidade (Baixa, Média, Alta e Crítica) |
| Mapa dos trens | Mapa da linha com as estações geradas a partir dos sensores cadastrados |
| Painel de relatórios | Totais de rotas, sensores, usuários e incidentes, além da lista de ocorrências recentes |

**Tipos de incidentes monitorados:** pane elétrica, pane no motor, superaquecimento dos freios, falha de comunicação, falha no GPS, obstáculo na via, descarrilamento, colisão/acidente, excesso de velocidade, sobrecarga de peso, queda de energia e incêndio.

## Tecnologias utilizadas

- **Linguagem/Framework de back-end:** PHP (com `mysqli`)
- **Linguagem/Framework de front-end:** HTML5, CSS3, JavaScript e Bootstrap 5.3
- **Banco de dados:** MySQL / MariaDB
- **Servidor local:** XAMPP (Apache + MySQL)
- **Microcontrolador/sensores (IoT):** _a definir (ex.: ESP32 com sensores de GPS, temperatura, vibração, presença e velocidade)_

## Estrutura do projeto

```
projeto-pampa-serra/
├── assets/         # Imagens e logo do projeto
├── database/       # Script do banco de dados (banco.sql) e criação do administrador
├── doc/            # Documentação e pesquisas do projeto
├── public/         # Telas do sistema (trens, rotas, sensores, mapa, incidentes, relatórios, usuários)
├── script/         # Scripts JavaScript
├── style/          # Estilos CSS
├── conexao.php     # Conexão com o banco de dados
├── index.php       # Tela de login
├── LICENSE         # Licença do projeto
└── README.md       # Este arquivo
```

Repositório: `https://github.com/MattosCode12/projeto-pampa-serra`

## Segurança

- Senhas armazenadas com hash (`password_hash` / `password_verify`);
- Proteção contra **CSRF** nos formulários;
- Regeneração do ID de sessão após o login;
- Controle de acesso por perfil (administrador x usuário comum);
- Saída de dados tratada com `htmlspecialchars` para evitar **XSS**;
- Conexão com o banco configurada com tratamento de erros e charset `utf8mb4`.

## Documentação

Os estudos e pesquisas realizados durante o projeto estão na pasta [`doc/`](doc/):

| Documento | Assunto |
|-----------|---------|
| [`identidade_visual.md`](doc/identidade_visual.md) | Cores, tipografia e estilo visual do sistema |
| [`pesquisa_scrum.md`](doc/pesquisa_scrum.md) | Estudo sobre a metodologia Scrum |
| [`pesquisa_XAMPP.md`](doc/pesquisa_XAMPP.md) | Estudo sobre o ambiente XAMPP |
| [`pesquisa_PDO.md`](doc/pesquisa_PDO.md) | Estudo sobre PDO |
| [`pesquisa_crud.md`](doc/pesquisa_crud.md) | Estudo sobre operações CRUD |

## Licença

Este projeto está sob a licença [MIT](LICENSE).
