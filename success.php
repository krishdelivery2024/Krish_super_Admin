<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">

    <title>Hello, world!</title>
  </head>
  <body>
    <div class="container mt-4">
    <div class="card">
      <div class="card-header">
       <h4 class="text-center">Success!</h4> 
       <h5 class="text-center">Vendor has been registered successfully.</h5> 
      </div>
    </div>
    </div>

    

    <!-- Optional JavaScript; choose one of the two! -->

    <!-- Option 1: jQuery and Bootstrap Bundle (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>

    <!-- Option 2: Separate Popper and Bootstrap JS -->
    <!--
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js" integrity="sha384-+sLIOodYLS7CIrQpBjl+C7nPvqq+FbNUBDunl/OZv93DB7Ln/533i8e/mZXLi/P+" crossorigin="anonymous"></script>
    -->
    <script src="dist/scripts/jquery.min.js"></script>
    <script>
$(document).on('change','#state',function(){
//   alert('change');
    $.ajax({
       url:'public/db-operation.php',
       method:'POST',
       data:'state_id='+$('#state').val()+'&find_city=1',
       success:function(data){
          // alert(data);
           $('#city').html(data);
       }
    });
});

$(document).on('change','#city',function(){
    $.ajax({
       url:'public/db-operation.php',
       method:'POST',
       data:'city_id='+$('#city').val()+'&find_area=1',
       success:function(data){
          // alert(data);
           $('#area').html(data);
       }
    });
});

$('#add_form').validate({
    rules:{
        mobile: {
            required:true,
            remote: {
                type:'post',
                url:'public/db-operation.php',
                data:{
                    'field': $('#mobile').attr('name'),
                    'validate_unique':1
                },
            }
        },
        email: {
            email:true,
            remote: {
                url:'public/db-operation.php',
                method:'POST',
                data:{
                    'field': $('#email').attr('name'),
                    'validate_unique':1,
                },
            }
        }
        
    },
    messages: {
                mobile: {
                   remote: 'Mobile already taken'
                },
                email: {
                        remote: 'E-Mail already taken'
                    }
            }
});

$(document).ready(function() { 
         
    $('#mobile').on('focusout',function(){
        $("#add_form").validate().element('#mobile');
    });
    
    $('#email').on('focusout',function(){
        $("#add_form").validate().element('#email');
    });
}); 
</script>
  </body>
</html>
