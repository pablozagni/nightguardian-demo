@extends('layouts.site', [
    'canonical' => rtrim((string) config('demo.site_url'), '/').($explicit ? '/'.$locale : '/'),
    'alternates' => true,
])

@section('body')
@php
    $github = 'https://github.com/pablozagni';
    $panel = rtrim((string) config('demo.panel_url'), '/');
    $icons = ['🔕', '🔎', '⚡', '🔔', '🔒', '🎁'];
    $sponsor = config('demo.sponsor_github_url');
    $mp = config('demo.donate_mp_url');
    $qr = $mp ? \App\Support\Qr::svg((string) $mp) : null;
    $d = __('site.how.diagram');
@endphp

@include('partials.nav')

<main>
    {{-- hero ------------------------------------------------------------------------------------------------ --}}
    <section class="hero">
        <div class="wrap">
            <div>
                <p class="eyebrow">{{ __('site.hero.eyebrow') }}</p>
                <h1>{{ __('site.hero.title') }}</h1>
                <p class="sub">{{ __('site.hero.subtitle') }}</p>
                <div class="actions">
                    <a class="btn btn-primary" href="{{ route('try') }}">{{ __('site.hero.cta_try') }} →</a>
                    <a class="btn btn-ghost" href="{{ $github }}/nightguardian" rel="noopener">{{ __('site.hero.cta_github') }}</a>
                </div>
                <p class="note">{{ __('site.hero.note') }}</p>
            </div>
            <div class="hero-shot">
                <img src="{{ asset('img/panel-issue-dark.png') }}" width="1440" height="1500" alt="{{ __('site.screens.issue') }}" fetchpriority="high">
            </div>
        </div>
    </section>

    {{-- problem --------------------------------------------------------------------------------------------- --}}
    <section class="section">
        <div class="wrap">
            <p class="eyebrow">{{ __('site.problem.title') }}</p>
            <h2>{{ __('site.problem.lead') }}</h2>
            <ul class="problems">
                @foreach (__('site.problem.points') as $point)
                    <li>{{ $point }}</li>
                @endforeach
            </ul>
        </div>
    </section>

    {{-- features -------------------------------------------------------------------------------------------- --}}
    <section class="section alt" id="features">
        <div class="wrap">
            <p class="eyebrow">{{ __('site.features.title') }}</p>
            <div class="grid cols-3" style="margin-top:1.4rem">
                @foreach (__('site.features.items') as $i => $feature)
                    <article class="card">
                        <span class="icon" aria-hidden="true">{{ $icons[$i] ?? '✦' }}</span>
                        <h3>{{ $feature['title'] }}</h3>
                        <p>{{ $feature['text'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- how it works ---------------------------------------------------------------------------------------- --}}
    <section class="section" id="how">
        <div class="wrap">
            <p class="eyebrow">{{ __('site.how.title') }}</p>
            <h2>{{ __('site.how.lead') }}</h2>

            <div class="diagram">
                <svg viewBox="0 0 920 210" role="img" aria-label="{{ __('site.how.lead') }}">
                    <defs>
                        <marker id="arrow" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="7" markerHeight="7" orient="auto-start-reverse"><path d="M0 0L10 5L0 10z" fill="#f5b942"/></marker>
                    </defs>
                    <g fill="#16224a" stroke="#34437a" stroke-width="1.5">
                        <rect x="10" y="70" width="170" height="70" rx="14"/>
                        <rect x="250" y="70" width="170" height="70" rx="14" stroke-dasharray="5 4"/>
                        <rect x="490" y="70" width="170" height="70" rx="14" fill="#1b2a5e" stroke="#f5b942"/>
                        <rect x="730" y="20" width="180" height="62" rx="14"/>
                        <rect x="730" y="128" width="180" height="62" rx="14"/>
                    </g>
                    <g text-anchor="middle">
                        <text x="95" y="102">{{ $d['app'] }}</text><text class="sub" x="95" y="122">+ {{ $d['sdk'] }}</text>
                        <text x="335" y="102">{{ $d['spool'] }}</text><text class="sub" x="335" y="122">storage/nightguardian</text>
                        <text x="575" y="102">{{ $d['collector'] }}</text><text class="sub" x="575" y="122">/api/v1/ingest</text>
                        <text x="820" y="56">{{ $d['panel'] }}</text>
                        <text x="820" y="164">{{ $d['alerts'] }}</text>
                    </g>
                    <g stroke="#f5b942" stroke-width="2" fill="none" marker-end="url(#arrow)">
                        <path d="M182 105H246"/>
                        <path d="M422 105H486"/>
                        <path d="M662 90L726 56"/>
                        <path d="M662 120L726 158"/>
                    </g>
                    <g text-anchor="middle">
                        <text class="sub" x="214" y="56">{{ $d['after'] }}</text>
                        <text class="sub" x="454" y="94">HTTPS</text>
                        <text class="sub" x="672" y="188">{{ $d['queue'] }}</text>
                    </g>
                </svg>
            </div>

            <ol class="steps">
                @foreach (__('site.how.steps') as $step)
                    <li>{{ $step }}</li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- screenshots ------------------------------------------------------------------------------------------ --}}
    <section class="section alt" id="screens">
        <div class="wrap">
            <p class="eyebrow">{{ __('site.screens.title') }}</p>
            <div class="shots">
                @foreach ([
                    ['panel-issue-dark.png', 'issue'],
                    ['panel-issue-feedback.png', 'feedback'],
                    ['error-page-'.$locale.'-dark.png', 'error_page'],
                    ['panel-tokens.png', 'tokens'],
                    ['panel-dashboard-dark.png', 'dashboard'],
                    ['panel-search.png', 'search'],
                ] as [$file, $key])
                    <figure class="shot">
                        <img src="{{ asset('img/'.$file) }}" loading="lazy" width="1200" height="750" alt="{{ __('site.screens.'.$key) }}">
                        <figcaption>{{ __('site.screens.'.$key) }}</figcaption>
                    </figure>
                @endforeach
            </div>
        </div>
    </section>

    {{-- install ---------------------------------------------------------------------------------------------- --}}
    <section class="section" id="install">
        <div class="wrap install">
            <p class="eyebrow">{{ __('site.install.title') }}</p>
            <div class="grid cols-3" style="margin-top:1.4rem">
                @php $steps = __('site.install.steps'); @endphp
                <article class="card">
                    <span class="num">1</span>
                    <h3>{{ $steps[0]['title'] }}</h3>
                    <p>{{ $steps[0]['text'] }}</p>
                    <pre class="code">git clone https://github.com/pablozagni/nightguardian.git
cd nightguardian
docker compose up -d --build
docker compose exec app php artisan \
  nightguardian:install --skip-migrate</pre>
                    <p style="margin-top:.8rem"><a href="{{ $github }}/nightguardian/blob/main/docs/deploy.md" rel="noopener">{{ __('site.install.guide') }} →</a></p>
                </article>
                <article class="card">
                    <span class="num">2</span>
                    <h3>{{ $steps[1]['title'] }}</h3>
                    <p>{{ $steps[1]['text'] }}</p>
                    <pre class="code">composer require nightguardian/laravel</pre>
                </article>
                <article class="card">
                    <span class="num">3</span>
                    <h3>{{ $steps[2]['title'] }}</h3>
                    <p>{{ $steps[2]['text'] }}</p>
                    <pre class="code">NIGHTGUARDIAN_URL=https://ng.example.com
NIGHTGUARDIAN_TOKEN=ng_...

php artisan nightguardian:test</pre>
                </article>
            </div>
        </div>
    </section>

    {{-- live demo ---------------------------------------------------------------------------------------------- --}}
    <section class="section alt" id="live">
        <div class="wrap">
            <div class="panel-box">
                <div>
                    <p class="eyebrow">{{ __('site.live.title') }}</p>
                    <h2>{{ __('site.hero.cta_try') }}</h2>
                    <p class="lead">{{ __('site.live.text') }}</p>
                    <div class="actions" style="margin-top:1.2rem">
                        <a class="btn btn-primary" href="{{ route('try') }}">{{ __('site.live.button') }} →</a>
                        <a class="btn btn-ghost" href="{{ $panel }}" rel="noopener">{{ __('site.live.panel') }}</a>
                    </div>
                </div>
                <div>
                    <dl class="cred">
                        <dt>{{ __('site.live.credentials') }}</dt>
                        <dd>{{ $panel }}</dd>
                        <dt>{{ __('site.live.email') }}</dt>
                        <dd>{{ config('demo.visitor_email') }}</dd>
                        <dt>{{ __('site.live.password') }}</dt>
                        <dd>{{ config('demo.visitor_password') }}</dd>
                    </dl>
                    <p class="muted" style="font-size:.85rem;margin:.8rem 0 0">{{ __('site.live.readonly') }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- SDKs --------------------------------------------------------------------------------------------------- --}}
    <section class="section" id="sdks">
        <div class="wrap">
            <p class="eyebrow">{{ __('site.sdks.title') }}</p>
            <h2>{{ __('site.sdks.lead') }}</h2>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>{{ __('site.sdks.platform') }}</th><th>{{ __('site.sdks.package') }}</th><th>{{ __('site.sdks.status') }}</th></tr></thead>
                    <tbody>
                        @foreach (__('site.sdks.rows') as $row)
                            <tr>
                                <td>{{ $row['platform'] }}</td>
                                <td><code>{{ $row['package'] }}</code></td>
                                <td><span class="badge {{ $row['ready'] ? 'ok' : 'wanted' }}">{{ $row['ready'] ? __('site.sdks.available') : __('site.sdks.wanted') }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card" style="margin-top:1.4rem">
                <h3>{{ __('site.sdks.wanted_title') }}</h3>
                <p>{{ __('site.sdks.wanted_text') }}</p>
                <div class="actions" style="margin-top:1rem">
                    <a class="btn btn-ghost btn-sm" href="{{ $github }}/nightguardian/blob/main/docs/protocol/v1.md" rel="noopener">{{ __('site.sdks.protocol') }}</a>
                    <a class="btn btn-ghost btn-sm" href="{{ $github }}/nightguardian/blob/main/docs/sdk-authors.md" rel="noopener">{{ __('site.sdks.authors') }}</a>
                </div>
            </div>
        </div>
    </section>

    {{-- repositories and docs ------------------------------------------------------------------------------- --}}
    <section class="section alt">
        <div class="wrap">
            <p class="eyebrow">{{ __('site.links.title') }}</p>
            <div class="linklist">
                <a href="{{ $github }}/nightguardian" rel="noopener">{{ __('site.links.collector') }} <span class="muted">· nightguardian</span></a>
                <a href="{{ $github }}/nightguardian-laravel" rel="noopener">{{ __('site.links.laravel') }} <span class="muted">· nightguardian-laravel</span></a>
                <a href="{{ $github }}/nightguardian-demo" rel="noopener">{{ __('site.links.demo') }} <span class="muted">· nightguardian-demo</span></a>
                <a href="{{ $github }}/nightguardian/blob/main/docs/protocol/v1.md" rel="noopener">{{ __('site.links.protocol') }}</a>
                <a href="{{ $github }}/nightguardian/blob/main/docs/deploy.md" rel="noopener">{{ __('site.links.deploy') }}</a>
                <a href="{{ $github }}/nightguardian/blob/main/ROADMAP.md" rel="noopener">{{ __('site.links.roadmap') }}</a>
            </div>
        </div>
    </section>

    {{-- support (only the buttons that have a URL) ---------------------------------------------------------- --}}
    @if ($sponsor || $mp)
        <section class="section" id="support">
            <div class="wrap">
                <div class="support {{ $qr ? 'has-qr' : '' }}">
                    <div>
                        <p class="eyebrow">{{ __('site.support.title') }}</p>
                        <h2>{{ __('site.support.text') }}</h2>
                        <div class="actions" style="margin-top:1.2rem">
                            @if ($sponsor)
                                <a class="btn btn-primary" href="{{ $sponsor }}" rel="noopener">♥ {{ __('site.support.github') }}</a>
                            @endif
                            @if ($mp)
                                <a class="btn {{ $sponsor ? 'btn-ghost' : 'btn-primary' }}" href="{{ $mp }}" rel="noopener">{{ __('site.support.mp') }}</a>
                            @endif
                        </div>
                    </div>
                    @if ($qr)
                        <div class="qr">
                            <div class="qr-box" role="img" aria-label="{{ __('site.support.qr') }}">{!! $qr !!}</div>
                            <small>{{ __('site.support.qr') }}</small>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    @endif
</main>

<footer class="footer">
    <div class="wrap">
        <span>{{ __('site.footer.made') }} <a href="https://www.z-system.com.ar" rel="noopener">z.system</a> 🇦🇷</span>
        <span>
            <a href="{{ $github }}/nightguardian-demo/blob/main/LICENSE" rel="noopener">{{ __('site.footer.license') }}</a> ·
            <a href="{{ $github }}/nightguardian-demo" rel="noopener">{{ __('site.footer.source') }}</a>
        </span>
    </div>
</footer>

<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'SoftwareApplication',
    'name' => 'NightGuardian',
    'applicationCategory' => 'DeveloperApplication',
    'operatingSystem' => 'Linux, Windows, macOS',
    'description' => __('site.meta.description'),
    'url' => rtrim((string) config('demo.site_url'), '/').'/',
    'license' => 'https://opensource.org/licenses/MIT',
    'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'USD'],
    'author' => ['@type' => 'Organization', 'name' => 'z.system', 'url' => 'https://www.z-system.com.ar'],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}
</script>
@endsection
