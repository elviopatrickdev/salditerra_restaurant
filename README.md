# 🍽️ Salditerra Restaurant Web Application

## 🌍 Sobre o Projeto
Este projeto é um website para **Salditerra Restaurant**, um restaurante de **culinária típica de Cabo Verde**, localizado em **Abuja, Nigéria**.

O sistema permite que clientes explorem o menu, façam pedidos e reservas online, enquanto administradores podem gerir produtos e utilizadores através de um painel administrativo.

O objetivo do projeto é demonstrar a construção de uma aplicação web completa utilizando tecnologias clássicas de desenvolvimento web, com foco em **usabilidade, organização do código e interação dinâmica**.

🌐 **Site online:**  
https://salditerra.wuaze.com/

---

# 💻 Tecnologias Utilizadas

- HTML5
- CSS3
- JavaScript (AJAX)
- JSON
- PHP
- MySQL

---

# ⚡ Funcionalidades Principais

- Exibição do catálogo de **pratos típicos cabo-verdianos**
- **Carrinho de compras dinâmico** com atualização em tempo real
- **Cálculo automático do total do carrinho**
- **Gestão de utilizadores**
  - Registo
  - Login
  - Edição de perfil
  - Logout
- **Área administrativa**
  - Adicionar produtos
  - Editar produtos
  - Remover produtos
  - Visualizar utilizadores
- **Reservas online**
- Importação de catálogo via **JSON (`catalog.json`)**
- Contagem de itens no carrinho em **tempo real**

---

# 📸 Screenshots

### Página Inicial
![Home](assets/screenshot_home.png)

### Carrinho
![Cart](assets/screenshot_cart.png)

### Painel Admin
![Admin](assets/screenshot_admin_dash.png)

---

# 📂 Estrutura de Ficheiros

## Front-end

| Ficheiro | Descrição |
|----------|-----------|
| `index.php` | Página inicial com destaque para os pratos |
| `cart.php` | Página do carrinho de compras |
| `profile.php` | Página de perfil do utilizador |
| `login-register.php` | Página de login e registo |
| `reservation.php` | Página de reservas online |
| `style.css` | Estilos globais do site |
| `scripts.js` | Funcionalidades interativas |

---

## Back-end / PHP

| Ficheiro | Descrição |
|----------|-----------|
| `config.php` | Configurações da base de dados |
| `get_products.php` | Obtenção dos produtos do catálogo |
| `add_product.php` | Adição de novos produtos |
| `edit_product.php` | Edição de produtos |
| `delete_product.php` | Remoção de produtos |
| `import_catalog.php` | Importação de catálogo via JSON |
| `add_to_cart.php` | Adicionar produto ao carrinho |
| `remove_from_cart.php` | Remover produto do carrinho |
| `update_cart.php` | Atualizar quantidades do carrinho |
| `totals.php` | Cálculo do total do carrinho |
| `cart-count.php` | Contagem de itens no carrinho |
| `process_login.php` | Processamento de login |
| `process_register.php` | Processamento de registo |
| `edit_user.php` | Edição de utilizador |
| `logout.php` | Logout |
| `admin.php` | Painel de administração |

---

## Dados e Recursos

| Pasta / Ficheiro | Descrição |
|-----------------|-----------|
| `catalog.json` | Dados iniciais do catálogo |
| `assets/` | Imagens e ícones |
| `uploads/` | Uploads de utilizadores e produtos |

---

# 🗄 Base de Dados

O sistema utiliza **MySQL** para armazenar:

- Utilizadores
- Produtos
- Reservas
- Itens do carrinho

A base de dados deve ser criada manualmente no MySQL e as tabelas podem ser importadas através de um ficheiro SQL incluído no projeto.

---

# 🛠 Requisitos

- Servidor web (Apache ou Nginx)
- PHP 7 ou superior
- MySQL
- Navegador moderno com JavaScript

Ambientes recomendados:

- XAMPP
- WAMP
- LAMP

---

# 🚀 Instalação Local

### 1️⃣ Clonar o repositório

```bash
git clone https://github.com/elviopatrickdev/salditerra_restaurant.git
