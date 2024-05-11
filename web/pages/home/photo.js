const setBase64Image = (src) => {
    document.getElementById('chat_nb_file').setAttribute("value", src);
    document.getElementById('chat_nb_snapshot').setAttribute("value", src);
  }
  
  const snapshot = () => {
  
      function clearphoto(photo) {
        const context = canvas.getContext("2d");
        context.fillStyle = "#AAA";
        context.fillRect(0, 0, canvas.width, canvas.height);
      
        const data = canvas.toDataURL("image/png");
        document.getElementById('snapshot_id').setAttribute("value", data);
        // console.log('typeof', typeof data, data)
      }
    
      function takepicture() {
        const context = canvas.getContext("2d");
        if (width && height) {
          canvas.width = width;
          canvas.height = height;
          context.drawImage(video, 0, 0, width, height);
      
          const data = canvas.toDataURL("image/png");
  
          document.getElementById('snapshot_id').setAttribute("value", data);
        }
      }
    
      const width = 200;    // On redimensionnera la photo pour avoir cette largeur
      let height = 0;     // Cela sera calculé à partir du flux d'entrée
      
      let streaming = false;
      
      let video = null;
      let canvas = null;
      let photo = null;
      let startbutton = null;
    
      function startup() {
        video = document.getElementById('video');
        canvas = document.getElementById('canvas');
        startbutton = document.getElementById('startbutton');
    
        function getBrowserName(userAgent) {
          // The order matters here, and this may report false positives for unlisted browsers.
        
        if (userAgent.includes("Firefox")) {
            // "Mozilla/5.0 (X11; Linux i686; rv:104.0) Gecko/20100101 Firefox/104.0"
          return 1;
        }
        return 0;
      }
    
      const isFirefox = getBrowserName(navigator.userAgent);
      console.log(isFirefox);
        
      if (isFirefox) {
        navigator.mediaDevices
        .getUserMedia({ video: true, audio: false })
        .then((stream) => {
            video.srcObject = stream;
            video.play();
        })
        .catch((err) => {
            console.error(`Une erreur est survenue : ${err}`);
        });
      }
      else {
        navigator.permissions.query({name: 'camera'}).then((permission) => {
        console.log("camera state", permission.state);
        video.srcObject = stream;
        video.play();
      }).catch((err) => {
        console.error(`Une erreur est survenue : ${err}`);
        });
      }
    
      video.addEventListener(
        "canplay",
        (ev) => {
        if (!streaming) {
          height = (video.videoHeight / video.videoWidth) * width;
          
          video.setAttribute("width", width);
          video.setAttribute("height", height);
          canvas.setAttribute("width", width);
          canvas.setAttribute("height", height);
          streaming = true;
        }},
        false
      );
        
      startbutton.addEventListener(
        "click",
        (ev) => {
            takepicture();
            ev.preventDefault();
        },
        false);
    
        clearphoto();
      }
      startup();
    }
    window.onload = snapshot