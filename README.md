# Simple nutrition counter

Counts nutrients as simple as possible (one tap per ingredient). Helps improving your daily nutrient intake to improve general health and brain function. It also calculates partially used ingredients.

- [Project state](#project-state)
- [Mission](#mission)
- [Usage](misc/usage.md)
- [Developer information](misc/dev_info.md)
  - [Model](misc/dev_info.md#model)
- [License](#license)

**Disclaimer:** Use the app and recommendations included in it at your own risk. This app is in development, the recommended amounts might be wrong.


Project state
----------------------------------------------------------

In use on a daily basis as a single user app since months, but it is still in development...

**Upcoming features**

- [ ] Improve food list **(started)**
- [ ] Finish nutrients tab **(started)**
- [ ] Code extensions like types per day entry
- [ ] Improve UI and make nicer **(started)**
- [ ] Code improvements
- When this is done food and nutrients data will be entered, and a few useful minor features may be added
- [ ] Maybe: The simple textarea might be replaced with a sortable html list (that already exists)

**Limitations**

Currently I am saving time for different projects by skipping features that I don't necessarily need as a single user.

- All multi user function like login, session, settings ...
- Forms for editing all the data (can be done im yml)
- Advanced features like importing food data via smartphone cam and AI (most likely this would cost fees)

**Simplifications (the best os no)**

- No complex frameworks
- No db will be used

<table>
  <tr>
    <td>Older</td>
    <td>Plan (will be nicer ;)</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td>
      <img src="misc/img.png" width="200">
    </td>
    <td>
      <img src="misc/design_1.png" width="200">
    </td>
    <td>
      <img src="misc/design_2.png" width="200">
    </td>
  </tr>
</table>


Mission
----------------------------------------------------------

Most people are deficient in one or more than one substance e.g. zinc, vitamin D, iron, ... 50% of US citizens are deficient in Magnesium, 30% are deficient in ... What if supply could be perfect? All of these substances are needed for something. Being able to track all vitamins, minerals, amino acids, fatty acids separately may make a big difference for general health and brain function. For example, Plant-based nutrition can lead to salt deficiency. Neurons need salt to function. There are may examples like this.

The problem with supplements: B12 cyanocobalamin isn't the real B12 but a cheap synthetic form, dosage while production often goes wrong and unexplored plant substances are missing. Of course it might make sense to use some supplements if you can't get enough from food. But in general food is the more natural (better) source (if free of substances like pesticides).

**Long term goal:** Handle all minimal daily logging in a single app as simple as possible (with as few clicks as possible). This isn't necessarily nutrition only but only all that can't be handled easier (most likely nutrition and daily expenses = actual consumption).

This kind of project might be AI proof because AI solution would be: it watches you while cooking and counts the calories. Do you want that?


LICENSE
----------------------------------------------------------

Copyright (C) Walter A. Jablonowski 2024, free for non-commercial users currently under AGPLv3 [License](https://choosealicense.com/licenses/agpl-3.0), \
commercial licensees must support the development

This app is build upon PHP and free software (see [credits](credits.md)).

[Privacy](https://walter-a-jablonowski.github.io/privacy.html) | [Legal](https://walter-a-jablonowski.github.io/imprint.html)
