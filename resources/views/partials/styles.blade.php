<style>
    :root { color-scheme: light dark; --bg: #f5f5fa; --card: #fff; --fg: #1d1d27; --muted: #6b6b7b; --line: #dcdce6; --accent: #4f46e5; --danger: #dc2626; --ok: #059669; --warn: #fef3c7; --warnfg: #92400e; }
    @media (prefers-color-scheme: dark) { :root { --bg: #0f0f17; --card: #181824; --fg: #ececf3; --muted: #9a9aae; --line: #2c2c3d; --accent: #818cf8; --danger: #f87171; --ok: #34d399; --warn: #3b2f12; --warnfg: #fcd34d; } }
    * { box-sizing: border-box; }
    body { margin: 0; background: var(--bg); color: var(--fg); font: 16px/1.55 system-ui, -apple-system, "Segoe UI", sans-serif; }
    main { max-width: 64rem; margin: 0 auto; padding: 2rem 1rem 3rem; }
    h1 { margin: 0 0 .25rem; font-size: 1.9rem; }
    h2 { margin: 2.5rem 0 .75rem; font-size: 1.25rem; }
    h3 { margin: 0 0 .4rem; font-size: 1.05rem; }
    a { color: var(--accent); }
    code { font: .88em ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; background: color-mix(in srgb, var(--fg) 8%, transparent); padding: .05em .35em; border-radius: .3em; }
    .lead { color: var(--muted); margin: 0 0 1rem; }
    .grid { display: grid; gap: 1rem; grid-template-columns: repeat(auto-fill, minmax(19rem, 1fr)); }
    .card { background: var(--card); border: 1px solid var(--line); border-radius: .75rem; padding: 1.1rem 1.2rem; display: flex; flex-direction: column; }
    .card p { margin: 0 0 .6rem; font-size: .93rem; }
    .card .expect { color: var(--muted); font-size: .85rem; }
    .card > form, .card > .button, .card > button, .card > .row { margin-top: auto; }
    .row { display: flex; flex-wrap: wrap; gap: .5rem; align-items: center; }
    .row form { margin: 0; }
    .button { display: inline-block; border: 0; border-radius: .5rem; padding: .55rem .95rem; background: var(--accent); color: #fff; font: inherit; font-weight: 600; font-size: .92rem; cursor: pointer; text-decoration: none; }
    .button.danger { background: var(--danger); }
    .button.ghost { background: transparent; color: var(--fg); border: 1px solid var(--line); }
    .button:hover { filter: brightness(1.08); }
    input[type=number] { width: 5.5rem; padding: .45rem .5rem; border: 1px solid var(--line); border-radius: .4rem; background: transparent; color: inherit; font: inherit; }
    label { font-size: .9rem; color: var(--muted); }
    pre { margin: 0; white-space: pre-wrap; word-break: break-word; font: .85rem/1.5 ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; }
    .flash { background: var(--card); border: 1px solid var(--ok); border-left-width: 5px; border-radius: .6rem; padding: .8rem 1rem; margin-bottom: 1rem; }
    .flash pre { margin-top: .3rem; color: var(--muted); }
    .warn { background: var(--warn); color: var(--warnfg); border-radius: .6rem; padding: .7rem 1rem; margin-bottom: 1rem; font-size: .92rem; }
    .result { margin-top: .6rem; padding: .6rem .8rem; border: 1px solid var(--line); border-radius: .5rem; }
    .status { background: var(--card); border: 1px solid var(--line); border-radius: .75rem; padding: 1rem 1.2rem; }
    footer { margin-top: 2.5rem; color: var(--muted); font-size: .9rem; }
    .muted { opacity: .8; }
</style>
