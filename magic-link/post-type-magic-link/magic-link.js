window.get_magic = () => {
  window.makeRequest( "GET", '/', { action: 'get', parts: jsObject.parts }, jsObject.rest_namespace )
  .done(function(data){
    window.load_magic( data )
  })
  .fail(function(e) {
    console.log(e)
    jQuery('#error').html(e)
  })
}
window.get_magic()

window.load_magic = ( data ) => {
  let content = jQuery('#api-content')
  let spinner = jQuery('.loading-spinner')

  content.empty()
  let html = ``
  data.forEach(v=>{
    html += `
         <div class="cell">
             ${window.lodash.escape(v.name)}
         </div>
     `
  })
  content.html(html)

  spinner.removeClass('active')

}

$('.dt_date_picker').datepicker({
  constrainInput: false,
  dateFormat: 'yy-mm-dd',
  changeMonth: true,
  changeYear: true,
  yearRange: "1900:2050",
}).each(function() {
  if (this.value && moment.unix(this.value).isValid()) {
    this.value = window.SHAREDFUNCTIONS.formatDate(this.value);
  }
})


$('#submit-form').on("click", function (){
  $(this).addClass("loading")
  let start_date = $('#start_date').val()
  let comment = $('#comment-input').val()
  let update = {
    start_date,
    comment
  }

  window.makeRequest( "POST", '/', { parts: jsObject.parts, update }, jsObject.rest_namespace ).done(function(data){
    window.location.reload()
  })
  .fail(function(e) {
    console.log(e)
    jQuery('#error').html(e)
  })
})
