const translations = {
    pt: {
        nav_intro: "Introdução",
        nav_install: "Instalação",
        nav_commands: "Comandos",
        nav_autodocs: "Auto Docs",
        hero_title: "Forje APIs e Frontends com Maestria",
        hero_subtitle: "O CoreStack Ecosystem é um conjunto de ferramentas 'database-first' para Laravel que gera APIs RESTful, frontends Quasar completos e documentação automática em segundos.",
        cta_start: "Começar Agora",
        feat_db_title: "Database First",
        feat_db_p: "Gere todo o seu backend a partir do esquema do seu banco de dados existente. Sem necessidade de definir modelos manualmente.",
        feat_arch_title: "Arquitetura Limpa",
        feat_arch_p: "Implementação nativa de padrões Service e Repository, garantindo um código organizado e fácil de manter.",
        feat_front_title: "Vue + Quasar",
        feat_front_p: "Gerador de interface administrativa completo usando Quasar Framework, pronto para produção e totalmente responsivo.",
        install_title: "Instalação Simples",
        install_subtitle: "Adicione o poder do CoreStack ao seu projeto Laravel existente.",
        publish_text: "Após instalar, publique os arquivos de configuração:",
        spa_setup_title: "💡 Configuração do SPA",
        spa_setup_p: "Para integrar o frontend Quasar diretamente no seu projeto Laravel como um SPA, utilize:",
        gen_title: "Geração de Código",
        gen_subtitle: "Comandos poderosos para acelerar seu fluxo de trabalho.",
        api_head: "Gerando API (Backend)",
        api_p: "Para criar uma API completa para uma tabela:",
        front_head: "Gerando Frontend",
        front_p: "Crie uma interface CRUD no Quasar para o seu recurso:",
        docs_title: "Documentação Automática",
        docs_subtitle: "Integração perfeita com o Laravel API Auto Docs.",
        docs_p: "O ecossistema CoreStack inclui o Laravel API Auto Docs, que analisa suas rotas, controllers e requests para gerar uma documentação Swagger/OpenAPI sempre atualizada.",
        docs_final: "Acesse /api-docs no seu navegador para ver sua API documentada automaticamente, com suporte a exemplos de resposta dinâmicos.",
        footer_base: "Inspirado pelo InfyOm Laravel Generator."
    },
    en: {
        nav_intro: "Introduction",
        nav_install: "Installation",
        nav_commands: "Commands",
        nav_autodocs: "Auto Docs",
        hero_title: "Forge APIs and Frontends with Mastery",
        hero_subtitle: "The CoreStack Ecosystem is a database-first set of tools for Laravel that generates RESTful APIs, complete Quasar frontends, and automatic documentation in seconds.",
        cta_start: "Get Started",
        feat_db_title: "Database First",
        feat_db_p: "Generate your entire backend from your existing database schema. No need to define models manually.",
        feat_arch_title: "Clean Architecture",
        feat_arch_p: "Native implementation of Service and Repository patterns, ensuring organized and maintainable code.",
        feat_front_title: "Vue + Quasar",
        feat_front_p: "Complete admin interface generator using Quasar Framework, production-ready and fully responsive.",
        install_title: "Simple Installation",
        install_subtitle: "Add CoreStack power to your existing Laravel project.",
        publish_text: "After installing, publish the configuration files:",
        spa_setup_title: "💡 SPA Setup",
        spa_setup_p: "To integrate the Quasar frontend directly into your Laravel project as an SPA, use:",
        gen_title: "Code Generation",
        gen_subtitle: "Powerful commands to accelerate your workflow.",
        api_head: "Generating API (Backend)",
        api_p: "To create a complete API for a table:",
        front_head: "Generating Frontend",
        front_p: "Create a CRUD interface in Quasar for your resource:",
        docs_title: "Automatic Documentation",
        docs_subtitle: "Seamless integration with Laravel API Auto Docs.",
        docs_p: "The CoreStack ecosystem includes Laravel API Auto Docs, which analyzes your routes, controllers, and requests to generate always-up-to-date Swagger/OpenAPI documentation.",
        docs_final: "Access /api-docs in your browser to see your automatically documented API, with support for dynamic response examples.",
        footer_base: "Inspired by InfyOm Laravel Generator."
    }
};

let currentLang = 'pt';

function updateContent() {
    const elements = document.querySelectorAll('[data-i18n]');
    elements.forEach(el => {
        const key = el.getAttribute('data-i18n');
        if (translations[currentLang][key]) {
            el.innerText = translations[currentLang][key];
        }
    });
}

document.getElementById('langSwitch').addEventListener('click', () => {
    currentLang = currentLang === 'pt' ? 'en' : 'pt';
    updateContent();
});

// Initialize
updateContent();
