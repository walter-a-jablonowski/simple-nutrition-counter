
- see ai.md
- added insert points in code as TASK

- [ ] applying to app, left orver tasks

  - most on clicks, ids and modal attribs were added from current app
  - see also readme for structure

  - [ ] we have the small buttons 2 times. currently using ids in one menur, needs to be class
    - Large: Toolbar in left column (d-none d-md-flex)
    - Mobile: Header buttons (d-md-none)
  - #sidebar #bottomNav
    - [ ] similar: info-circle (originally btn now link)
    - [ ] similar: bi-person (originally btn now link)
  - [ ] header with user select exists 2 times: second time for own layout tab 3 (favoritesLayout)


- [x] right layout col devides in goals and food layout
- [-] in mobile left and right are visible but food layout is hidden
- [-] add left-layout-col .. limit the styles

- [x] Header (less imp menu functions, looks like content)
  - alternative: make wider and or use like status bar
- Menu
  - [ ] Menu bar might have overlay secondary menu
  - [x] Menu items have tooltip
  - [x] Moved main menues up cause easier handling (touch above)

- [x] hide the section for the entries instead of the widg
  - section is hidden when adding display none, but the rest of the content doesn't move up on mobile
  - some class or style or tag structure causes the problem

  ```
  ... This button in smartphones currently seems to hide .nutrition-widgets. Instead use it to make ... smaller

   --
  
  I can see that something happens with the section when I press the button, but the rest of the content (.nutrition-widgets and below) doesn't move up on mobile. Some class or style or tag structure may causes the problem.
  ```
