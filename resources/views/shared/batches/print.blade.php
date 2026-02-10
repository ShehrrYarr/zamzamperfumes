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

</html>