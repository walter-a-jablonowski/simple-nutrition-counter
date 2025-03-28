
In the folder "diagr" make a tool that reads all tsv files from the folder which is defined in config.yml > source

Columns in the tsv file:

- time
- type
- food
- calories
- fat
- carbs
- amino
- salt
- price
- json with details

tsv files are seperated with spaces (no tab), at least 2 spaces befor each column

Make a new html page in that folder with seperate line charts for:

- calories
- fat
- carbs
- amino
- salt
- price

Each source file contains the data of a day (the date is the file name).
x-axis of the charts: dates
y-axis: sum for the nutrient for this day (e.g. fat sum)

units: kcal for calories, grams for nutrients

Also add a horizontal line in each chart for the limit defined in config.yml (if any)

Keep js php, html, js code and styles in seperate files


Sample data file: 2024-05-09.tsv

00:00:00  F   20g Amino NaDuRia Pur    71.4   1.3   2.4   11.4  0    0     {fat: {}, amino: {}, vit: {}, min: {}, sec: {}}
00:00:00  F       1 Falafel            467.4  13.1  70.3  13.3  3.4  0     {fat: {}, amino: {}, vit: {}, min: {}, sec: {}}
00:00:00  F       1 Duplo              100.3  6     10.2  1.1   0    0.49  {fat: {}, amino: {}, vit: {}, min: {}, sec: {}}
00:00:00  F       1 Duplo              100.3  6     10.2  1.1   0    0.49  {fat: {}, amino: {}, vit: {}, min: {}, sec: {}}
00:00:00  F     1/2 Cashew N           603    47.1  22.2  21    0    1.15  {fat: {}, amino: {}, vit: {}, min: {}, sec: {}}
00:00:00  F       1 Frosta Gesch       393.8  9.8   48.4  25.1  4.4  3.49  {fat: {}, amino: {}, vit: {}, min: {}, sec: {}}
00:00:00  F    30ml Olivenöl           270    30    0     0     0    1.02  {fat: {}, amino: {}, vit: {}, min: {}, sec: {}}
00:00:00  F     1/2 Chick R Bio        136.5  2.9   16.1  7.6   0.4  0.43  {fat: {}, amino: {}, vit: {}, min: {}, sec: {}}
