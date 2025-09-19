
Entering new prices

- Accept existing prices just by clicking "Accept"
  - this will update the date only

Misc

- deal prices are orange


Import
----------------------------------------------------------

1. verify.php: looks if price changes are reasonable (command line or browser)

  - Normalizes numbers (comma to point, removes spaces/thousand separators)
  - Compares the effective current price (prefers price, falls back to dealPrice) with the effective new price (prefers staged price, falls back to dealPrice)
  - Flags potentially unlikely changes using simple heuristics:
    - absolute jump ≥ 2.00€
    - relative jump ≥ 50%
    - new ≥ 2× current
    - new ≤ 0.5× current
    - non-positive new price
    - no current price (informational)

2. import.php (browser)
