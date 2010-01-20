function TabView(id, current){
    if(typeof(TabView.cnt) == "undefined"){
        TabView.init();
    }
    current = (typeof(current) == "undefined") ? 0 : current;
    this.newTab(id, current);
}
TabView.init = function(){
    TabView.cnt = 0;
    TabView.arTabView = new Array();
}
TabView.switchTab = function(TabViewIdx, TabIdx){
    TabView.arTabView[TabViewIdx].TabView.switchTab(TabIdx);
}
TabView.prototype.newTab = function(id, current){
    var TabViewElem, idx = 0, el = '', elTabs = '', elPages = '';
    TabViewElem = document.getElementById(id);
    TabView.arTabView[TabView.cnt] = TabViewElem;
    this.TabElem = TabViewElem;
    this.TabElem.TabView = this;
    this.tabCnt = 0;
    this.arTab = new Array();
    // Loop throught the elements till the object with
    // classname 'Tabs' is obtained
    elTabs = TabViewElem.firstChild;
    while(elTabs.className != "Tabs" )elTabs = elTabs.nextSibling;
    el = elTabs.firstChild;
    do{
        if(el.tagName == "A"){
            el.href = "javascript:TabView.switchTab(" + TabView.cnt + "," + idx + ");";
            this.arTab[idx] = new Array(el, 0);
            this.tabCnt = idx++;
        }
    }while (el = el.nextSibling);

    // Loop throught the elements till the object with
    // classname 'Pages' is obtained
    elPages = TabViewElem.firstChild;
    while (elPages.className != "Pages")elPages = elPages.nextSibling;
    el = elPages.firstChild;
    idx = 0;
    do{
        if(el.className == "Page"){
            this.arTab[idx][1] = el;
            idx++;
        }
    }while (el = el.nextSibling);
    this.switchTab(current);
    // Update TabView Count
    TabView.cnt++;
}
TabView.prototype.switchTab = function(TabIdx){
    var Tab;
    if(this.TabIdx == TabIdx)return false;
    for(idx in this.arTab){
        Tab = this.arTab[idx];
        if(idx == TabIdx){
            Tab[0].className = "ActiveTab";
            Tab[1].style.display = "block";
            Tab[0].blur();
        }else{
            Tab[0].className = "InactiveTab";
            Tab[1].style.display = "none";
        }
    }
    this.TabIdx = TabIdx;
   
}

    t1 = new TabView('TabView1');


