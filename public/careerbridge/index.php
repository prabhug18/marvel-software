<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Career Bridge - UK Study Form</title>
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }
    body, html {
      height: 100%;
      font-family: Arial, sans-serif;
      background: url('careerbridge/images/background.png') no-repeat center center/cover;
      position: relative;
      min-height: 100vh;
    }
    .logo {
      position: absolute;
      top: 20px;
      left: 20px;
      width: 200px;
      z-index: 10;
    }
    .badge {
      position: absolute;
      top: 20px;
      right: 20px;
      width: 150px;
      z-index: 10;
    }
    .form-container {
      position: absolute;
      top: 60%;
      left: 40%;
      transform: translate(-50%, -50%);
      background: white;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.3);
      max-width: 440px;
      width: 100%;
      z-index: 2;
      font-size: 1.1em;
    }
    .form-container h2 {
      font-size: 2em;
      color: #c41230;
      margin-bottom: 20px;
      text-align: center;
    }
    form {
      display: flex;
      flex-direction: column;
      gap: 15px;
    }
    input, textarea, button {
      padding: 12px;
      font-size: 1.1em;
      border: 1px solid #ccc;
      border-radius: 6px;
    }
    button {
      background-color: #c41230;
      color: white;
      border: none;
      cursor: pointer;
      font-weight: bold;
      transition: background-color 0.3s ease;
    }
    button:hover {
      background-color: #a50e26;
    }
    .girl-img {
      position: absolute;
      bottom: 0;
      right: 0;
      width: 550px;
      max-width: 60vw;
      z-index: 1;
    }
    @media (max-width: 991px) {
      .form-container {
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        padding: 20px 10px;
      }
      .girl-img {
        width: 300px;
      }
      .logo {
        width: 140px;
      }
      .badge {
        width: 70px;
      }
    }
    @media (max-width: 767px) {
      .logo, .badge {
        position: static;
        display: block;
        margin: 0 auto 10px auto;
      }
      .form-container {
        position: static;
        margin: 30px 10px 0 10px;
        max-width: 100%;
        box-shadow: 0 2px 6px rgba(0,0,0,0.15);
        left: unset;
        top: unset;
        transform: none;
      }
      .girl-img {
        display: none;
      }
    }
    @media (max-width: 400px) {
      .form-container {
        padding: 10px 2px;
      }
      .form-container h2 {
        font-size: 1.1em;
      }
    }
  </style>
</head>
<body>

  <img src="/careerbridge/images/logo.png" alt="Career Bridge Logo" class="logo" />
  <img src="/careerbridge/images/badge.png" alt="Badge" class="badge" />

  <div class="form-container">
    <h2>Apply to Top UK Universities</h2>
    <form id="uk-application-form" autocomplete="off">
      <input type="text" name="name" placeholder="Full Name" required />
      <input type="email" name="email" placeholder="Email Address" required />
      <input type="tel" name="phone" placeholder="Phone Number" required />
      <textarea name="message" rows="4" placeholder="Your Message" required></textarea>
      <button type="submit">Submit Application</button>
      <div id="form-msg" style="margin-top:10px;"></div>
    </form>
  </div>

  <img src="/careerbridge/images/girl.png" alt="Girl holding UK flag" class="girl-img" />

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>
    $(function() {
      $('#uk-application-form').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = form.find('button[type=submit]');
        var msg = $('#form-msg');
        msg.html('');
        btn.prop('disabled', true).text('Submitting...');
        var data = form.serialize();
        // Client-side validation
        var errors = {};
        form.find('input, textarea').each(function() {
          if (!$(this).val()) {
            errors[$(this).attr('name')] = 'This field is required.';
          }
        });
        var email = form.find('input[name=email]').val();
        if (email && !/^\S+@\S+\.\S+$/.test(email)) {
          errors['email'] = 'Enter a valid email.';
        }
        var phone = form.find('input[name=phone]').val();
        if (phone && !/^[0-9\-\+\s]{7,20}$/.test(phone)) {
          errors['phone'] = 'Enter a valid phone number.';
        }
        if (Object.keys(errors).length > 0) {
          var html = '<ul style="color:red;">';
          $.each(errors, function(k, v) { html += '<li>' + v + '</li>'; });
          html += '</ul>';
          msg.html(html);
          btn.prop('disabled', false).text('Submit Application');
          return;
        }
        // AJAX submit
       

        $.post('/careerbridge/submit_uk_form.php', data, function(response) {
          if (response.success) {
            msg.html('<span style="color:green;">' + response.message + '</span>');
            form[0].reset();
          } else if (response.errors) {
            var html = '<ul style="color:red;">';
            $.each(response.errors, function(k, v) { html += '<li>' + v + '</li>'; });
            html += '</ul>';
            msg.html(html);
          } else {
            msg.html('<span style="color:red;">' + response.message + '</span>');
          }
          btn.prop('disabled', false).text('Submit Application');
        }, 'json').fail(function() {
          msg.html('<span style="color:red;">Server error. Please try again later.</span>');
          btn.prop('disabled', false).text('Submit Application');
        });
      });
    });
  </script>

</body>
</html>
