@extends('layouts.app')

@section('title', 'Home')

@section('content')
<section class="hero-section text-white d-flex flex-column justify-content-center align-items-center" style="min-height: 75vh;">
  <div class="container text-center" data-aos="fade-up">
    <h1 class="hero-title">Benvenuto nella tua banca di fiducia</h1>
    <p class="hero-subtitle">Tecnologia. Sicurezza. Persone.</p>
  </div>
</section>

<section class="info-section">
  <div class="container">
    <div class="row text-center">
      <div class="col-md-4 mb-4" data-aos="fade-right">
        <div class="info-box h-100">
          <h4>Innovazione</h4>
          <p>Investiamo costantemente in tecnologie all'avanguardia per offrire servizi bancari digitali semplici e sicuri.</p>
        </div>
      </div>
      <div class="col-md-4 mb-4" data-aos="fade-up">
        <div class="info-box h-100">
          <h4>Sicurezza</h4>
          <p>Ogni transazione è protetta con i più alti standard di sicurezza, per garantire la massima tranquillità.</p>
        </div>
      </div>
      <div class="col-md-4 mb-4" data-aos="fade-left">
        <div class="info-box h-100">
          <h4>Supporto Umano</h4>
          <p>Un team di consulenti sempre al tuo fianco, sia online che nelle filiali fisiche in tutta Italia.</p>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
