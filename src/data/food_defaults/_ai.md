
Please make a new file with the nutritional data from @_in.txt and use the format in @_blank_ai.yml

- Keep the yml formatting as it is, leave out-commented lines as they are
  and remove no blanks before the value
- Fill in values far as possible, if a value is missing add a 0 in this field
- If value for a single thing are multiple times in the source data, choose one and add all as a comment behind the field
  - kcal: prefer Atwater Specific over Atwater General
  - fat: refer: lipid (fat) over fat (NLEA = Nutrition Labeling and Education Act)
- Names mapping
  - PUFA 18:3 n-3 c,c,c (ALA) = Alpha-linolenic acid (ALA)
  - PUFA 22:6 n-3 (DHA)       = Docosahexaenoic acid (DHA)  
  - PUFA 18:2 n-6 c,c         = Linoleic acid  
- Add nutritional values with no unit, just the float or int and use the unit in the comment above it
- Leave the following fields blank: acceptable, comment
- lastUpdate: YYYY-MM-DD
