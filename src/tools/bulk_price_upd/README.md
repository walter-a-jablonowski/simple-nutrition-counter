
This uses no nutri lib functions for reading data but local functions

Usage
----------------------------------------------------------

Entering new prices

- Accept existing prices just by clicking "Accept"
  - this will update the date only

Misc

- deal prices are orange


Import
----------------------------------------------------------

1. verify.php: looks if price changes are reasonable (command line or browser)

  - browser has all lines, command line only if errors
  - Flags unlikely changes using simple heuristics:
    - absolute jump ≥ 2.00€
    - relative jump ≥ 50%
    - new ≥ 2× current
    - new ≤ 0.5× current
    - non-positive new price
    - no current price (informational)

2. import.php (browser)

3. remove import.yml when done

