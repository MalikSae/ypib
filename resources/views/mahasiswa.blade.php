<table>
    <tr>
      <th>Nama</th>
      <th>Npm</th>
      <th>Jenis Kelamin</th>
      <th>Alamat</th>
    </tr>
    @foreach($mahasiswa as $row )
    <tr>
        <td>{{ $row->nama}}</td>
        <td>{{ $row->npm}}</td>
        <td>{{ $row->jenisKelamin}}</td>
        <td>{{ $row->alamat}}</td>
    </tr>
    @endforeach
</table>