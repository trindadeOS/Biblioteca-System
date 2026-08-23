<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

    *{
        margin:0;
        padding:0;
        box-sizing:border-box;
        font-family:'Poppins', sans-serif;
    }

    body{
        background:#f4f7fb;
        color:#1e293b;
        min-height:100vh;
        display:flex;
        overflow-x: hidden;
    }

    .sidebar{
        width:260px;
        background:#111827;
        color:white;
        height:100vh;
        position:fixed;
        left:0;
        top:0;
        padding:30px 20px;
        z-index: 100;
    }

    .sidebar h2{
        font-size:28px;
        font-weight:700;
        margin-bottom:40px;
        text-align:center;
    }

    .sidebar a{
        color:#cbd5e1;
        text-decoration:none;
        display:block;
        padding:14px 18px;
        border-radius:12px;
        transition:0.3s;
        margin-bottom:10px;
        cursor:pointer;
    }

    .sidebar a:hover, .sidebar a.active{
        background:#2563eb;
        color:white;
    }

    .logout{
        margin-top:40px;
    }

    .logout a{
        background:#dc2626;
        color:white;
    }

    .logout a:hover{
        background:#b91c1c;
    }

    .main{
        margin-left:260px;
        width:100%;
        padding:40px 50px;
    }

    .topbar{
        display:flex;
        justify-content:space-between;
        align-items:center;
        margin-bottom:40px;
    }

    .topbar h1{
        font-size:34px;
        margin-bottom:5px;
    }

    .topbar p{
        color:#64748b;
    }

    .cards{
        display:grid;
        grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
        gap:20px;
        margin-bottom:35px;
    }

    .card{
        background:white;
        padding:25px;
        border-radius:20px;
        box-shadow:0 5px 15px rgba(0,0,0,0.05);
        transition:0.3s;
    }

    .card:hover{
        transform:translateY(-5px);
    }

    .card h3{
        color:#64748b;
        font-size:16px;
        margin-bottom:10px;
    }

    .card h2{
        font-size:34px;
        margin-bottom:0;
    }

    .table-box{
        background:white;
        border-radius:20px;
        padding:25px;
        box-shadow:0 5px 15px rgba(0,0,0,0.05);
    }

    input, select, textarea {
        padding:12px;
        margin:8px 0;
        width:100%;
        border:1px solid #dbe2ea;
        border-radius:12px;
        outline:none;
        font-family:'Poppins', sans-serif;
        font-size:14px;
    }

    input:focus, select:focus {
        border-color:#2563eb;
    }

    button, .btn {
        padding:12px 24px;
        border:none;
        border-radius:12px;
        cursor:pointer;
        font-weight:600;
        transition: 0.3s;
        display: inline-block;
        text-decoration: none;
    }

    .btn-blue{
        background:#2563eb;
        color:white;
    }

    .btn-blue:hover{
        background:#1d4ed8;
    }

    .msg{
        margin-top:15px;
        font-weight:600;
        color:#16a34a;
    }

    /* ==========================================================
       MODO RESPONSIVO: ATIVADO AUTOMATICAMENTE NO CELULAR
       ========================================================== */
    @media (max-width: 900px) {
        body {
            flex-direction: column !important;
        }
        
        /* Transforma a sidebar escura numa barra horizontal no topo */
        .sidebar {
            width: 100% !important;
            height: auto !important;
            position: relative !important;
            padding: 12px !important;
            display: flex !important;
            flex-direction: row !important;
            flex-wrap: wrap !important;
            justify-content: center !important;
            gap: 6px !important;
        }
        
        /* Oculta o título "Biblioteca" no celular para sobrar espaço */
        .sidebar h2 {
            display: none !important;
        }
        
        /* Transforma os links em botões horizontais menores */
        .sidebar a {
            margin-bottom: 0 !important;
            padding: 8px 14px !important;
            font-size: 13px !important;
            display: inline-block !important;
            border-radius: 8px !important;
        }
        
        /* Cola o botão de Sair junto com os outros links */
        .logout {
            margin-top: 0 !important;
        }
        
        /* Reajusta a área de conteúdo para ocupar a tela cheia */
        .main {
            margin-left: 0 !important;
            padding: 20px !important;
            width: 100% !important;
        }
        
        /* Organiza os cards um abaixo do outro de forma limpa */
        .cards {
            grid-template-columns: 1fr !important;
            gap: 15px !important;
        }
    }
</style>