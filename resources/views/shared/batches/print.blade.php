{{-- <!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Print Barcode {{ $batch->barcode }}</title>

    <style>
        @page {
            size: {
                    {
                    $w
                }
            }

            in {
                    {
                    $h
                }
            }

            in;
            margin: 0;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
        }

        body {
            font-family: Arial, sans-serif;
        }

        .label {
            width: {
                    {
                    $w
                }
            }

            in;

            height: {
                    {
                    $h
                }
            }

            in;
            box-sizing: border-box;
            padding: 0.08in;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 6px;
            overflow: hidden;
        }

        .top {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 8px;
            font-size: 10px;
        }

        .name {
            font-weight: 700;
            font-size: 11px;
            line-height: 1.1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 70%;
        }

        .barcode-wrap {
            display: flex;
            justify-content: center;
        }

        #barcode {
            width: 100%;
            height: auto;
        }

        .code {
            text-align: center;
            font-size: 10px;
            letter-spacing: 1px;
            font-weight: 700;
        }

        .meta {
            display: flex;
            justify-content: space-between;
            font-size: 9px;
            color: #111;
            gap: 8px;
        }

        /* Hide UI on print (we have none), but keep safe */
        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body>
    <div class="label">
        <div class="top">
            <div class="name">{{ $batch->perfume?->name ?? 'Perfume' }}</div>
            <div>{{ $batch->shop?->code ?? '' }}</div>
        </div>

        <div class="barcode-wrap">
            <svg id="barcode"></svg>
        </div>

        <div class="code">{{ $batch->barcode }}</div>

        <div class="meta">
            <div>Qty: {{ $batch->quantity }}</div>
            <div>
                @if($batch->sell_price !== null) Sell: {{ $batch->sell_price }} @endif
            </div>
        </div>
    </div>

    <!-- JsBarcode CDN -->
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
    <script>
        (function(){
      const value = @json($batch->barcode);

      // Render barcode
      JsBarcode("#barcode", value, {
        format: "CODE128",
        displayValue: false,
        margin: 0,
        height: 45
      });

      // Auto open print dialog
      window.onload = function () {
        setTimeout(() => window.print(), 150);
      };
    })();
    </script>
</body>

</html> --}}

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Print Barcode {{ $batch->barcode }}</title>

    <style>
        @page {
            size: {
                    {
                    $w
                }
            }

            in {
                    {
                    $h
                }
            }

            in;
            margin: 0;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
        }

        body {
            font-family: Arial, sans-serif;
        }

        .label {
            width: {
                    {
                    $w
                }
            }

            in;

            height: {
                    {
                    $h
                }
            }

            in;
            box-sizing: border-box;
            padding: 0.10in;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 6px;
            overflow: hidden;
        }

        /* Top header: centered name, optional shop code on right */
        .top {
            display: grid;
            grid-template-columns: 1fr auto;
            align-items: center;
            gap: 8px;
        }

        .name {
            font-weight: 800;
            font-size: 14px;
            /* ✅ bigger */
            line-height: 1.1;
            text-align: center;
            /* ✅ centered */
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .shopcode {
            font-size: 10px;
            font-weight: 700;
            color: #111;
            white-space: nowrap;
        }

        .barcode-wrap {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2px 0;
        }

        /* ✅ shrink barcode ~30% by scaling container */
        .barcode-scale {
            transform: scale(0.70);
            transform-origin: center;
            width: 100%;
        }

        #barcode {
            width: 100%;
            height: auto;
            display: block;
        }

        .code {
            text-align: center;
            font-size: 11px;
            letter-spacing: 1px;
            font-weight: 800;
            margin-top: -2px;
        }

        .sell {
            text-align: center;
            font-size: 12px;
            /* ✅ bigger */
            font-weight: 800;
            color: #111;
            margin-top: 2px;
        }

        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body>
    <div class="label">

        <div class="top">
            <div class="name">{{ $batch->perfume?->name ?? 'Perfume' }}</div>
            {{-- <div class="shopcode">{{ $batch->shop?->code ?? '' }}</div> --}}
        </div>

        <div class="barcode-wrap">
            <div class="barcode-scale">
                <svg id="barcode"></svg>
            </div>
        </div>

        <div class="code">{{ $batch->barcode }}</div>

        @if($batch->sell_price !== null)
        <div class="sell">Sell: {{ number_format((float)$batch->sell_price, 2) }}</div>
        @endif

    </div>

    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
    <script>
        (function(){
          const value = @json($batch->barcode);

          // ✅ smaller barcode: lower height + smaller font impact
          JsBarcode("#barcode", value, {
            format: "CODE128",
            displayValue: false,
            margin: 0,
            height: 32  // was 45 (≈ 30% smaller)
          });

          window.onload = function () {
            setTimeout(() => window.print(), 150);
          };
        })();
    </script>
</body>

</html>