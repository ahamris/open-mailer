<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Template Builder &middot; {{ $template->name }} &middot; CLOM</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    <link href="https://unpkg.com/grapesjs/dist/css/grapes.min.css" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; background: #1a1a2e; color: #e0e0e0; overflow: hidden; }

        .builder-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 3.5rem;
            padding: 0 1rem;
            background: #16213e;
            border-bottom: 1px solid #2a2a4a;
        }
        .builder-topbar__left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .builder-topbar__back {
            color: #8892b0;
            text-decoration: none;
            font-size: .8125rem;
            display: flex;
            align-items: center;
            gap: .375rem;
            transition: color .15s;
        }
        .builder-topbar__back:hover { color: #ccd6f6; }
        .builder-topbar__name {
            font-size: .9375rem;
            font-weight: 600;
            color: #ccd6f6;
        }
        .builder-topbar__right {
            display: flex;
            align-items: center;
            gap: .5rem;
        }
        .builder-topbar__status {
            font-size: .75rem;
            color: #8892b0;
            margin-right: .5rem;
        }
        .builder-btn {
            display: inline-flex;
            align-items: center;
            gap: .375rem;
            padding: .5rem 1rem;
            font-size: .8125rem;
            font-weight: 500;
            border-radius: .375rem;
            border: none;
            cursor: pointer;
            transition: background .15s, color .15s;
        }
        .builder-btn--save {
            background: #4361ee;
            color: #fff;
        }
        .builder-btn--save:hover { background: #3a56d4; }
        .builder-btn--save:disabled { opacity: .6; cursor: not-allowed; }
        .builder-btn--ghost {
            background: transparent;
            color: #8892b0;
            border: 1px solid #2a2a4a;
        }
        .builder-btn--ghost:hover { color: #ccd6f6; border-color: #4a4a6a; }

        /* GrapesJS overrides to match dark theme */
        .gjs-one-bg { background-color: #1a1a2e !important; }
        .gjs-two-color { color: #ccd6f6 !important; }
        .gjs-three-bg { background-color: #16213e !important; }
        .gjs-four-color, .gjs-four-color-h:hover { color: #4361ee !important; }
    </style>
</head>
<body>
    <div class="builder-topbar">
        <div class="builder-topbar__left">
            <a href="/admin/templates" class="builder-topbar__back">
                <svg style="width:.875rem;height:.875rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                Back to templates
            </a>
            <div class="builder-topbar__name">{{ $template->name }}</div>
        </div>
        <div class="builder-topbar__right">
            <span class="builder-topbar__status" id="save-status"></span>
            <button type="button" class="builder-btn builder-btn--ghost" onclick="previewTemplate()">
                <svg style="width:.875rem;height:.875rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                Preview
            </button>
            <button type="button" class="builder-btn builder-btn--save" id="save-btn" onclick="saveTemplate()">
                <svg style="width:.875rem;height:.875rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                Save
            </button>
        </div>
    </div>

    <div id="gjs"></div>

    <script src="https://unpkg.com/grapesjs"></script>
    <script src="https://unpkg.com/grapesjs-preset-newsletter"></script>
    <script>
        const existingHtml = @json($template->html_body ?? '');
        const existingGrapesJson = @json($template->grapes_json ?? null);
        const templateId = @json($template->id);
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        const editor = grapesjs.init({
            container: '#gjs',
            height: 'calc(100vh - 3.5rem)',
            width: 'auto',
            plugins: ['gjs-preset-newsletter'],
            pluginsOpts: {
                'gjs-preset-newsletter': {}
            },
            storageManager: false,
            // Load existing content
            components: existingHtml || '<div style="padding:2rem;text-align:center;color:#999;">Drag components here to build your email template</div>',
        });

        // If we have saved GrapesJS project data, load it
        if (existingGrapesJson) {
            try {
                const projectData = typeof existingGrapesJson === 'string' ? JSON.parse(existingGrapesJson) : existingGrapesJson;
                editor.loadProjectData(projectData);
            } catch (e) {
                console.warn('Could not load GrapesJS project data:', e);
            }
        }

        function saveTemplate() {
            const btn = document.getElementById('save-btn');
            const status = document.getElementById('save-status');
            btn.disabled = true;
            status.textContent = 'Saving...';

            const payload = {
                html: editor.getHtml(),
                css: editor.getCss(),
                grapes_json: JSON.stringify(editor.getProjectData()),
            };

            fetch('/admin/templates/' + templateId + '/builder', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload),
            })
            .then(function(res) {
                if (!res.ok) throw new Error('Save failed (' + res.status + ')');
                return res.json();
            })
            .then(function() {
                status.textContent = 'Saved';
                status.style.color = '#64ffda';
                setTimeout(function() { status.textContent = ''; status.style.color = ''; }, 3000);
            })
            .catch(function(err) {
                status.textContent = 'Error: ' + err.message;
                status.style.color = '#ff6b6b';
            })
            .finally(function() {
                btn.disabled = false;
            });
        }

        function previewTemplate() {
            const html = editor.getHtml();
            const css = editor.getCss();
            const fullHtml = '<html><head><style>' + css + '</style></head><body>' + html + '</body></html>';
            const win = window.open('', '_blank');
            win.document.write(fullHtml);
            win.document.close();
        }

        // Keyboard shortcut: Ctrl/Cmd + S to save
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                saveTemplate();
            }
        });
    </script>
</body>
</html>
