window.addEventListener("load", function () {

    // store tabs variables
    var tabs = document.querySelectorAll("ul.nav-tabs > li");

    for (i = 0; i < tabs.length; i++) {
        tabs[i].addEventListener("click", switchTab);
    }

    function switchTab(event) {
        event.preventDefault();
        var clickedTab = event.currentTarget;
        //console.log('tab=' + clickedTab);
        var anchor = event.target;
        //console.log('anchor=' + anchor);
        var activePaneID = anchor.getAttribute("href");
        //console.log('anchorT=' + anchor.innerText);

        // do not kill the tab system if the svg is clicked
        // stay on the current tab
        if (anchor.innerText === undefined) {
            return;
        }
        document.querySelector("ul.nav-tabs li.active").classList.remove("active");
        document.querySelector(".tab-pane.active").classList.remove("active");
        clickedTab.classList.add("active");
        document.querySelector(activePaneID).classList.add("active");
        //console.log('id=' + activePaneID);
    }
});