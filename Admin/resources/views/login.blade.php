<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>User Login</title>

  <style>
    :root{
      --bg1:#0f172a;      /* slate-900 */
      --bg2:#0b1030;      /* deep indigo */
      --acc1:#60a5fa;     /* blue-400 */
      --acc2:#34d399;     /* emerald-400 */
      --acc3:#f472b6;     /* pink-400 */
      --text:#e5e7eb;     /* gray-200 */
      --muted:#94a3b8;    /* slate-400 */
      --error:#ff4d6d;
      --success:#8ef0a0;
      --ring: rgba(96,165,250,.5);
      --glass: rgba(255,255,255,.06);
      --glass-strong: rgba(255,255,255,.12);
      --shadow: 0 20px 40px rgba(0,0,0,.45);
      --radius: 18px;
    }

    /* Base Reset */
    *,*::before,*::after{box-sizing:border-box}
    html,body{height:100%}
    body{
      margin:0;
      font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, "Apple Color Emoji","Segoe UI Emoji";
      color:var(--text);
      background: radial-gradient(1200px 800px at 10% 10%, rgba(79,70,229,.18), transparent 60%),
                  radial-gradient(1000px 700px at 90% 20%, rgba(236,72,153,.12), transparent 65%),
                  radial-gradient(900px 700px at 50% 100%, rgba(16,185,129,.12), transparent 60%),
                  linear-gradient(160deg, var(--bg1), var(--bg2));
      display:grid;
      place-items:center;
      padding:24px;
      overflow:hidden;
    }

    /* Floating blobs (decorative) */
    .blob{
      position:fixed; filter: blur(60px); opacity:.35; z-index:0; pointer-events:none;
      animation: float 18s ease-in-out infinite;
    }
    .blob.b1{ width:420px; height:420px; background:radial-gradient(circle at 30% 30%, var(--acc1), transparent 60%); top:-80px; left:-80px; animation-delay:0s;}
    .blob.b2{ width:520px; height:520px; background:radial-gradient(circle at 70% 40%, var(--acc2), transparent 60%); bottom:-120px; right:-90px; animation-delay:3s;}
    .blob.b3{ width:380px; height:380px; background:radial-gradient(circle at 40% 70%, var(--acc3), transparent 60%); top:40%; left:60%; animation-delay:6s;}
    @keyframes float {
      0%,100%{ transform: translateY(0) translateX(0) scale(1); }
      50%{ transform: translateY(-14px) translateX(10px) scale(1.03); }
    }

    /* Card */
    .card{
      position:relative; z-index:1;
      width:min(420px, 92vw);
      background: linear-gradient(180deg, rgba(255,255,255,.08), rgba(255,255,255,.04));
      backdrop-filter: blur(14px);
      border: 1px solid rgba(255,255,255,.12);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      padding:28px;
      animation: rise .65s cubic-bezier(.2,.7,.2,1) 1;
    }
    @keyframes rise {
      from{ transform: translateY(14px); opacity:0 }
      to{ transform: translateY(0); opacity:1 }
    }

    .card-header{
      display:flex; align-items:center; gap:12px; margin-bottom:18px;
    }
    .logo{
      width:42px; height:42px; border-radius:12px;
      background: conic-gradient(from 140deg, var(--acc1), var(--acc2), var(--acc3), var(--acc1));
      box-shadow: 0 8px 24px rgba(100,116,139,.35);
    }
    .title{
      font-weight:700; font-size:1.35rem; letter-spacing:.3px;
    }
    .subtitle{
      color:var(--muted); font-size:.9rem; margin-bottom:18px;
    }

    /* Alerts */
    .alert{ border-radius:12px; padding:10px 12px; font-size:.94rem; line-height:1.3; margin-bottom:12px; }
    .alert.success{ color:#0f5132; background: linear-gradient(180deg, rgba(16,185,129,.16), rgba(16,185,129,.10)); border:1px solid rgba(16,185,129,.28); }
    .alert.error{ color:#681a1e; background: linear-gradient(180deg, rgba(239,68,68,.16), rgba(239,68,68,.10)); border:1px solid rgba(239,68,68,.28); }

    /* Form */
    form{ margin-top:6px; }
    .field{
      display:grid; gap:8px; margin-bottom:14px;
    }
    label{ color:#c7d2fe; font-size:.92rem; letter-spacing:.2px; }
    .control{
      position:relative;
    }
    .input{
      width:100%; padding:14px 44px 14px 44px;
      border-radius:12px; border:1px solid rgba(255,255,255,.16);
      background: rgba(8, 13, 40, .35);
      color:var(--text); font-size:1rem;
      outline:none;
      transition: box-shadow .2s, border-color .2s, background .2s, transform .06s;
    }
    .input:hover{ border-color: rgba(255,255,255,.22); }
    .input:focus{ border-color: var(--acc1); box-shadow: 0 0 0 4px var(--ring); background: rgba(8,13,40,.42);}
    .leading, .trailing{
      position:absolute; top:0; bottom:0; display:grid; place-items:center; width:40px;
      color:#9fb5d1; opacity:.9;
    }
    .leading{ left:0 }
    .trailing{ right:0; cursor:pointer; user-select:none }
    .trailing:hover{ color:#cfe1ff }

    .row{
      display:flex; align-items:center; justify-content:space-between; gap:10px; margin:4px 2px 16px;
      color:var(--muted); font-size:.9rem;
    }
    .remember{ display:flex; align-items:center; gap:8px; cursor:pointer; }
    .checkbox{ width:18px; height:18px; border-radius:6px; appearance:none; border:1px solid rgba(255,255,255,.22); background: rgba(255,255,255,.02); display:grid; place-items:center; }
    .checkbox:checked{ background: linear-gradient(180deg, var(--acc1), #2563eb); border-color: transparent; }
    .checkbox:focus{ outline: 3px solid var(--ring); outline-offset: 2px; }

    .link{ color:#bfdbfe; text-decoration:none; border-bottom:1px dashed rgba(191,219,254,.35); padding-bottom:2px; }
    .link:hover{ color:#e0f2fe; border-color:transparent; }

    .btn{
      width:100%; padding:14px 16px; border:none; border-radius:12px;
      background: linear-gradient(135deg, #2563eb, #06b6d4);
      color:white; font-weight:700; letter-spacing:.3px; font-size:1rem;
      cursor:pointer; transition: transform .06s ease, box-shadow .2s ease, filter .2s ease;
      box-shadow: 0 10px 24px rgba(37,99,235,.35);
    }
    .btn:hover{ transform: translateY(-1px); box-shadow: 0 14px 28px rgba(37,99,235,.45); }
    .btn:active{ transform: translateY(0); filter: saturate(1.05); }
    .btn[disabled]{ opacity:.7; cursor:not-allowed; transform:none; }

    .foot{
      margin-top:14px; color:var(--muted); font-size:.82rem; text-align:center;
    }

    /* Small screens */
    @media (max-width: 420px){
      .title{ font-size:1.2rem }
      .card{ padding:22px }
    }
  </style>
</head>
<body>

  <!-- Decorative background blobs -->
  <div class="blob b1"></div>
  <div class="blob b2"></div>
  <div class="blob b3"></div>

  <main class="card" role="main" aria-labelledby="loginTitle">
    <div class="card-header">
      <div class="logo" aria-hidden="true"></div>
      <div>
        <div id="loginTitle" class="title">Welcome back</div>
        <div class="subtitle">Sign in to continue to your dashboard</div>
      </div>
    </div>

    {{-- Flash + Validation messages (accessible) --}}
    @if(session('status'))
      <div class="alert success" role="status" aria-live="polite">
        {{ session('status') }}
      </div>
    @endif

    @if ($errors->any())
      <div class="alert error" role="alert" aria-live="assertive">
        {{ $errors->first() }}
      </div>
    @endif

    <form id="loginForm" method="POST" action="{{ route('login.post') }}" novalidate>
      @csrf

      <!-- Username -->
      <div class="field">
        <label for="username">Username</label>
        <div class="control">
          <span class="leading" aria-hidden="true">
            <!-- user icon -->
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
              <path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Z" stroke="currentColor" stroke-width="1.6"/>
              <path d="M3.5 20.4a8.5 8.5 0 0 1 17 0" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
            </svg>
          </span>
          <input
            class="input"
            id="username"
            name="username"
            type="text"
            value="{{ old('username') }}"
            autocomplete="username"
            placeholder="e.g. admin"
            required
          />
        </div>
      </div>

      <!-- Password -->
      <div class="field">
        <label for="password">Password</label>
        <div class="control">
          <span class="leading" aria-hidden="true">
            <!-- lock icon -->
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
              <rect x="4" y="10" width="16" height="10" rx="2" stroke="currentColor" stroke-width="1.6"/>
              <path d="M8 10V7a4 4 0 0 1 8 0v3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
            </svg>
          </span>
          <input
            class="input"
            id="password"
            name="password"
            type="password"
            autocomplete="current-password"
            placeholder="••••••••"
            required
          />
          <button class="trailing" type="button" id="togglePw" aria-label="Show password">
            <!-- eye icon -->
            <svg id="eyeIcon" width="18" height="18" viewBox="0 0 24 24" fill="none">
              <path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12Z" stroke="currentColor" stroke-width="1.6"/>
              <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.6"/>
            </svg>
          </button>
        </div>
      </div>

      <div class="row">
        <label class="remember">
          <input class="checkbox" type="checkbox" name="remember" />
          <span>Remember me</span>
        </label>
        <a class="link" href="#" onclick="event.preventDefault()">Forgot password?</a>
      </div>

      <button class="btn" id="submitBtn" type="submit">
        Sign in
      </button>

      <div class="foot">By continuing you agree to our Terms & Privacy Policy.</div>
    </form>
  </main>

  <script>
    // Show/Hide password
    (function(){
      const pw = document.getElementById('password');
      const toggle = document.getElementById('togglePw');
      const eye = document.getElementById('eyeIcon');

      toggle?.addEventListener('click', () => {
        const isPwd = pw.type === 'password';
        pw.type = isPwd ? 'text' : 'password';
        toggle.setAttribute('aria-label', isPwd ? 'Hide password' : 'Show password');
        // simple eye toggle stroke trick
        eye.style.opacity = isPwd ? .85 : 1;
      });

      // Prevent double submit & show loading state
      const form = document.getElementById('loginForm');
      const btn  = document.getElementById('submitBtn');
      form?.addEventListener('submit', () => {
        if(btn){ btn.disabled = true; btn.textContent = 'Signing in…'; }
      });
    })();
  </script>
</body>
</html>
