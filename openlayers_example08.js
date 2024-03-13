let map;
let vectorLayer;
let vectorSource;
let tooltipOverlay;
let markerLayer;
let coordinates;
const myCompanyPos = {
  lon: 139.70338433807126,
  lat: 35.685213788374384,
};

window.onload = function(){
  setValues({});
  lon = myCompanyPos.lon;
  lat = myCompanyPos.lat;
  coordinate = [lon, lat];
  map = createMap(coordinate);

  // ベクターレイヤーの追加
  vectorSource = new ol.source.Vector();
  vectorLayer = new ol.layer.Vector({
    source: vectorSource,
    style: new ol.style.Style({
      image:  new ol.style.Circle({
          'radius' : 7,
          'stroke' : new ol.style.Stroke({
              'color' : 'rgba(255, 255, 255, 1)',
              'width' : 2,
          }),
          'fill' : new ol.style.Fill({
              'color' : 'rgba(0, 120, 240, 1)',
          })
      }),
    })
  });
  map.addLayer(vectorLayer);

  // ツールチップ用のオーバーレイを作成
  tooltipOverlay = new ol.Overlay({
    element: document.getElementById('tooltip'),
    offset: [0, -15],
    positioning: 'bottom-center'
  });
  map.addOverlay(tooltipOverlay);

  setPoint(coordinate, null);
  getList();

  // 地図クリックイベント
  map.on('click', function(e) {
    setPoint(ol.proj.transform(e.coordinate, 'EPSG:3857', 'EPSG:4326'), null);
  });
  // マップ上でマウスを移動するとツールチップを表示する
  map.on('pointermove', function (event) {
    var feature = map.forEachFeatureAtPixel(event.pixel, function (feature) {
        return feature;
    });
    if (feature) {
        var coordinate = event.coordinate;
        tooltipOverlay.setPosition(coordinate);
        var lonlat = ol.proj.toLonLat(coordinate);
        var name = feature.get('name');
        tooltipOverlay.element.innerHTML = `
          ${(name) ? '<div class="tooltip">' + name + '</div>' : ''}
        `;
        tooltipOverlay.element.style.display = 'block';
    } else {
        tooltipOverlay.element.style.display = 'none';
    }
  });
  // ボタンのイベント定義
  $('#button_mycompany').on('click', function(e) {
    lon = myCompanyPos.lon;
    lat = myCompanyPos.lat;
    coordinate = [lon, lat];
    setPoint(coordinate, null, true);
  });
  $('#button_save').on('click', function(e) {
    saveData();
  });
  $('#button_addnew').on('click', function(e) {
    setValues({});
  });
  $('#button_delete').on('click', function(e) {
    deleteData();
  });
  $(document).on('click', '#list .item', function(){
    let dt = $(this).data('dt');
    let name = $(this).data('name');
    let lon = $(this).data('lon');
    let lat = $(this).data('lat');
    let url = $(this).data('url');
    let remarks = $(this).data('remarks');
    let param = {
      dt: dt,
      lon: lon,
      lat: lat,
      name: name,
      url: url,
      remarks: remarks,
    };
    setValues(param);
    coordinate = [lon, lat];
    setPoint(coordinate, param, true);
  });

}

function createMap(coordinate) {
  return new ol.Map({
    target: 'map',
    layers: [
      new ol.layer.Tile({
        source: new ol.source.OSM()
      })
    ],
    view: new ol.View({
      center: ol.proj.fromLonLat(coordinate),
      zoom: 18
    }),
  });
}
function setPoint(coordinate, info, setCenter=false) {
  // ポイントオブジェクトを作成
  var feature = new ol.Feature({
    geometry: new ol.geom.Point(ol.proj.fromLonLat(coordinate)),
    name: (info) ? info['name'] : null,
  });

  // 指定位置に移動
  if (setCenter) {
    map.getView().animate({
      center: ol.proj.fromLonLat(coordinate),
      duration: 500,
    });  
  }

  // ポイントをいったんクリアしてから追加する
  vectorSource.clear();
  vectorSource.addFeature(feature);

  //経緯度表示    
  $('#ctrl_lon').val(coordinate[0]);
  $('#ctrl_lat').val(coordinate[1]);
}
function saveData(){
  let hostUrl= './src/point.php';
  let dt = $('#ctrl_dt').val();
  let lon = $('#ctrl_lon').val();
  let lat = $('#ctrl_lat').val();
  let ptName = $('#ctrl_name').val();
  let url = $('#ctrl_url').val();
  let remarks = $('#ctrl_remarks').val();
  if (!lon || !lat || !ptName) {
    myAlert('入力してください');
    return;
  }
  $.ajax({
    url: hostUrl,
    type:'POST',
    dataType: 'json',
    data : { mode: 'save', dt: dt, lon : lon, lat : lat, name: ptName, url: url, remarks: remarks },
    timeout:3000,
  }).done(function(result) {
    if (result) {
      myAlert('登録しました');
      setValues({});
      // リストを再表示
      getList();
    }
  }).fail(function(XMLHttpRequest, textStatus, errorThrown) {
    alert("error");
  })
}
function deleteData(){
  let deleteFunc = function() {
    let hostUrl= './src/point.php';
    let dt = $('#ctrl_dt').val();
    if (!dt) {
      return;
    }
    $.ajax({
      url: hostUrl,
      type:'POST',
      dataType: 'json',
      data : { mode: 'delete', dt: dt, },
      timeout:3000,
    }).done(function(result) {
      if (result) {
        myAlert('削除しました');
        setValues({});
        // リストを再表示
        getList();
      }
    }).fail(function(XMLHttpRequest, textStatus, errorThrown) {
      alert("error");
    })
  };
  myConfirmYesNo('削除しますか？', deleteFunc);

}
function getList(){
  let hostUrl= './src/getlist.php';
  $.ajax({
    url: hostUrl,
    type:'POST',
    dataType: 'json',
    data : { },
    timeout:3000,
  }).done(function(result) {
    if (result) {
      var html = '';
      result.list.forEach(element => {
        let data = ' data-name="' + element.name + '"';
        data += ' data-dt="' + element.dt + '"';
        data += ' data-lon="' + element.lon + '"';
        data += ' data-lat="' + element.lat + '"';
        data += ' data-url="' + element.url + '"';
        data += ' data-remarks="' + element.remarks + '"';
        html += '<tr>';
        html += '<td class="info">';
        html += '<a class="item" ' + data + ' href="#">' + element.name + '</a>';
        if (element.url) {
          html += '<a class="url" href="'+ element.url +'" target="_blank"></a>';
        }
        html += '</td>';
        html += '</tr>';
      });
      if (html) {
        html = '<table class="point-list u-full-width"><tbody>' + html + '</tbody></table>';
      }
      $('#list').html(html);      
    }
  }).fail(function(XMLHttpRequest, textStatus, errorThrown) {
    alert("error");
  })
}
function setValues(param) {
  $('#ctrl_dt').val(getValue(param, 'dt'));
  $('#ctrl_lon').val(getValue(param, 'lon'));
  $('#ctrl_lat').val(getValue(param, 'lat'));
  $('#ctrl_name').val(getValue(param, 'name'));
  $('#ctrl_url').val(getValue(param, 'url'));
  $('#ctrl_remarks').val(getValue(param, 'remarks'));
  if ($('#ctrl_dt').val()) {
    $('#button_save').val('変更');
    $('#button_delete').show();
  } else {
    $('#button_save').val('追加');
    $('#button_delete').hide();
  }
}
function getValue(param, key) {
  let val = param[key];
  if (val == undefined) val = '';
  return val;
}
function myAlert(content, title='') {
  $.alert({
    title: title,
    type: 'blue',
    useBootstrap: false,
    boxWidth: '30%',
    content: content,
  });
}
function myConfirmYesNo(content, yesFunc=null, noFunc=null, title='') {
  $.confirm({
    title: title,
    type: 'blue',
    useBootstrap: false,
    boxWidth: '30%',
    content: content,
    buttons: {
      confirm: {
        text:'はい',
        btnClass: 'btn-blue',
        action: function () {
          if (yesFunc) yesFunc();
        },
      },
      cancel: {
        text:'いいえ',
        action: function () {
          if (noFunc) noFunc();
        },
      },
    }
  });
}
