<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Todo App') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                flex-direction: column;
            }

            header {
                padding: 1.5rem 2rem;
                display: flex;
                justify-content: flex-end;
                gap: 1rem;
            }

            header a {
                color: white;
                text-decoration: none;
                padding: 0.5rem 1.25rem;
                border-radius: 0.375rem;
                font-size: 0.875rem;
                font-weight: 500;
                transition: all 0.2s;
            }

            header a:hover {
                background: rgba(255, 255, 255, 0.2);
            }

            header a.register {
                background: white;
                color: #667eea;
            }

            header a.register:hover {
                background: #f0f0f0;
            }

            main {
                flex: 1;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                padding: 2rem;
                text-align: center;
            }

            .icon {
                width: 80px;
                height: 80px;
                background: white;
                border-radius: 1rem;
                display: flex;
                align-items: center;
                justify-content: center;
                margin-bottom: 2rem;
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            }

            .icon svg {
                width: 48px;
                height: 48px;
                color: #667eea;
            }

            h1 {
                color: white;
                font-size: 3rem;
                font-weight: 700;
                margin-bottom: 1rem;
            }

            p {
                color: rgba(255, 255, 255, 0.9);
                font-size: 1.25rem;
                max-width: 500px;
                line-height: 1.6;
                margin-bottom: 2.5rem;
            }

            .cta {
                display: inline-block;
                background: white;
                color: #667eea;
                padding: 1rem 2.5rem;
                border-radius: 0.5rem;
                font-size: 1.125rem;
                font-weight: 600;
                text-decoration: none;
                transition: transform 0.2s, box-shadow 0.2s;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            }

            .cta:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
            }

            .features {
                display: flex;
                gap: 2rem;
                margin-top: 4rem;
                flex-wrap: wrap;
                justify-content: center;
            }

            .feature {
                background: rgba(255, 255, 255, 0.1);
                padding: 1.5rem;
                border-radius: 0.75rem;
                width: 200px;
                backdrop-filter: blur(10px);
            }

            .feature-icon {
                font-size: 2rem;
                margin-bottom: 0.75rem;
            }

            .feature h3 {
                color: white;
                font-size: 1rem;
                font-weight: 600;
                margin-bottom: 0.5rem;
            }

            .feature p {
                color: rgba(255, 255, 255, 0.8);
                font-size: 0.875rem;
                margin-bottom: 0;
            }

            @media (max-width: 640px) {
                h1 {
                    font-size: 2rem;
                }

                p {
                    font-size: 1rem;
                }

                .features {
                    flex-direction: column;
                    align-items: center;
                }
            }
        </style>
    </head>
    <body>
        @if (Route::has('login'))
            <header>
                @auth
                    <a href="{{ url('/dashboard') }}">Dashboard</a>
                @else
                    <a href="{{ route('login') }}">Entrar</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="register">Cadastrar</a>
                    @endif
                @endauth
            </header>
        @endif

        <main>
            <div class="icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>

            <h1>Todo App</h1>
            <p>Organize suas tarefas de forma simples e eficiente. Crie, gerencie e acompanhe suas atividades diárias.</p>

            @auth
                <a href="{{ url('/dashboard') }}" class="cta">Ir para o Dashboard</a>
            @else
                <a href="{{ route('register') }}" class="cta">Comece Agora</a>
            @endauth

            <div class="features">
                <div class="feature">
                    <div class="feature-icon">+</div>
                    <h3>Criar Tarefas</h3>
                    <p>Adicione novas tarefas rapidamente</p>
                </div>
                <div class="feature">
                    <div class="feature-icon">-</div>
                    <h3>Organizar</h3>
                    <p>Mantenha tudo em ordem</p>
                </div>
                <div class="feature">
                    <div class="feature-icon">*</div>
                    <h3>Concluir</h3>
                    <p>Marque tarefas como feitas</p>
                </div>
            </div>
        </main>
    </body>
</html>
