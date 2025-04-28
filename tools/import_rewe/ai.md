
Try to make a function in /import_rewe/lib.php that can find food price data from rewe.de based on a product search

It takes an argument as string like

  REWE Beste Wahl Aqua Mia Plus Apfel 0,75

Then query like https://shop.rewe.de/productList?search=REWE%20Beste%20Wahl%20Aqua%20Mia%20Plus%20Apfel%200%2C75

Be sure to use the right encoding for the url.

We are in the product list now. Look at the above url, where you can see how the html structure of the page looks like. Also add verification for possible changes in the html structure (invent something for that that is reliable) and return ['error', 'html structure changed'].

If our search string was good enough we should see only one entry in the article list. If multiple articles are in the list, return ['error', 'multiple articles'] cause we can't be sure if we get the right article.

If we have only one list entry read the price. If the price element has a class "productOfferPrice" it is a deal price, return ['success', 1.00, true]. Second field returned is price and third is dealPrice true or false.

<div class="search-service-productPrice productPrice" aria-hidden="true">1,29 €</div>
<div class="search-service-productOfferPrice productOfferPrice" aria-hidden="true">0,65 €</div>

No food found, return: ['missing']


### Demo

Make a script try.php that loops src\data\bundles\Default_JaneDoe@example.com-24080101000000\foods and reads:

- all yml files with no underscore in front of the file name
- as well as all yml files "-this.yml" in sub folders

If the field vendor is "Rewe" use the function to get the price and append the list of all articles to a text file.

Between calls of the function wait for a random number of ms. Enable the script to run endless.
