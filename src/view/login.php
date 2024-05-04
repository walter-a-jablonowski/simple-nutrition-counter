<!--

has the same style as error_page, only one class removed (see below)

TASKS:

- when there was entered something in repeat it is a register attempz
  else if is login attempt (mode = login)
- add a simple html code that checks if repeat is the same

- just create fil sys fld with my@mail.de => my_mail_de as name
- use a simple session_start()

-->
<div class="d-flex align-items-center justify-content-center ext-page-container">
  <div class="ext-round-box">  <!-- same as error page, removed text-center -->

    <h2 class="mb-3">Nutri Counter</h2>
    <form>
      <div class="mb-3">
        <input id="email" type="email" class="form-control form-control-sm" placeholder="Mail">
      </div>
      <div class="mb-3">
        <input id="password" type="password" placeholder="Password" class="form-control form-control-sm">
      </div>
      <div class="mb-3">
        <a href="#registerForm" class="mb-3 small text-decoration-none" data-bs-toggle="collapse" role="button">
        Register
      </div>
      </a>
      <div id="registerForm" class="collapse">
        <div class="mb-3">
          <input id="repeatPassword" type="password" class="form-control form-control-sm" placeholder="Repeat password">
        </div>
      </div>
      <div class="mb-3">
        <button type="submit" class="btn btn-sm ext-btn">Login</button>
      <div>
    </form>

  </div>
</div>
