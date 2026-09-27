# Devoluções Mentor — Tema WordPress

Landing page de formulário de devolução de produtos para a Mentor Company Lda.

## Instalação

1. Copie a pasta `DevolucoesMentor` para `wp-content/themes/`
2. No painel WordPress, vá a **Aparência → Temas** e ative **Devoluções Mentor**
3. Crie uma nova página (ex: "Devoluções")
4. Em **Atributos da página**, selecione o template **Formulário de Devolução**
5. Publique a página e defina-a como página inicial (opcional)

## Personalização do Logo

Vá a **Aparência → Personalizar → Identidade do site** e carregue o logo da Mentor. O header exibirá o logo centralizado automaticamente.

## Funcionalidades

- Formulário completo com validação client-side e server-side
- Upload de fotografias (até 10, obrigatório), vídeos (até 3) e documentos
- Submissão via AJAX com envio de email ao administrador
- Design responsivo com fonte Barlow e cor principal `#04445f`
- Drag & drop para uploads de ficheiros

## Estrutura

```
DevolucoesMentor/
├── style.css              # Cabeçalho do tema WP
├── functions.php          # Enqueue, AJAX handler, email
├── header.php             # Header com logo centralizado
├── footer.php             # Footer com copyright
├── page-devolucoes.php    # Template do formulário
├── index.php              # Fallback
└── assets/
    ├── css/main.css       # Estilos principais
    ├── js/form.js         # Interações do formulário
    └── images/logo-mentor.svg
```

## Requisitos

- WordPress 5.8+
- PHP 7.4+
