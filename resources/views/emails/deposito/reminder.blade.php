@component('mail::message')
# Reminder Deposito Jatuh Tempo

Halo, berikut daftar deposito yang jatuh tempo:

@foreach ($depositos as $deposito)
- **Nama:** {{ $deposito->nama_nasabah }}
- **Rekening:** {{ $deposito->no_rekening }}
- **Nominal:** Rp {{ number_format($deposito->nominal, 0, ',', '.') }}
- **Jatuh Tempo:** {{ $deposito->tanggal_jatuh_tempo }}
- **Jenis:** {{ $deposito->jenis_rollover }}
@endforeach

Terima kasih.
@endcomponent
