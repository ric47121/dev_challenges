<template>
  <div id="container">
    <button @click="login()">login</button>
    you: {{user}} 
    <button @click="join()">join</button>
    <button @click="refresh()">refresh</button>
    <!-- <button @click="read()">read</button> -->
    <div class="vote">
      <ul id="voteList">
        <li v-for="vote in validVotes"
            :key="vote"
            :class="{voted: you.vote === vote}"
            @click="emitVote(vote)">{{vote}}</li>
      </ul>
    </div>
    <div class="members">
      <h3>
        {{status}} issue #<input id="issue" type="number" v-model="issue" @change="refresh()" /> • Connected {{members.length}}
      </h3>
      <ul id="memberList">
        <li :key="member.name" v-for="member in members">
          <!-- <div class="status">{{member.vote ? '✅' : ''}}</div> -->
          <div class="status">{{member.status == 'voted' ? '✅' : ''}}</div>
          <div class="name">{{member.name}}</div>
          <!-- <div class="vote">{{member.vote ? member.vote : '-'}}</div> -->
          <div class="vote">{{status == 'reveal' ? member.value : '-'}}</div>
        </li>
      </ul>
    </div>

    <p>🎹 Get complete instructions at <a href="https://github.com/workana/hiring_challenge">Workana Hiring Challenge</a>.</p>
    <hr />
<pre style="text-align: left;">
        <strong>PHP res:</strong>
        {{responsesDemo.php}}

        <strong>Node res:</strong>
        {{responsesDemo.node}}

        <strong>status:</strong>
        {{responsesDemo.status}}
</pre>

  </div>
</template>

<script>


export default {
  name: 'Lobby',
  data() {
    return {
      ip: '161.35.57.160',
      user: '?',
      issue: 234,
      status: 'voting',
      validVotes: [1,2,3,5,8,13,20,40,'?'],
      members: [
        // {name: 'Julian (you)', vote: false},
        // {name: 'Flor', vote: false},
        // {name: 'Gino', vote: false}
      ],
      responsesDemo: {
        php: null,
        node: null,
        status: null
      }
    };
  },
  computed: {
    you() { 
    
      let res = this.members.find(o => o.name == this.user)
      
      if( res ){
        return res
      }else{
        return {name: '? (you)', vote: false}
      }

    },
  },
  async mounted() {
    
    this.demoResponses();
    this.responsesDemo.status = this.ip
  },
  methods: {
          login(){
            this.responsesDemo.status = this.ip
            let nom = prompt('ingrese su nombre')
            this.user = nom
          },
          refresh(){
            this.demoResponses()
          },
          async join() {

            const requestOptions = {
              method: "POST",
              headers: { "Content-Type": "application/json" },
              body: JSON.stringify({ name: this.user})
            };
            const response = await fetch('http://'+ this.ip +':8081/issue/' + this.issue + '/join', requestOptions);
            const data = await response.json();
            // this.postId = data.id;
            this.responsesDemo.status = JSON.stringify(data);

            this.refresh()
        },

        async emitVote(vote) {
          console.log(vote)
          // if (vote === this.you.vote) {
          //   this.you.vote = false;
          //   return;
          // }
          // this.you.vote = vote;


           const requestOptions = {
              method: "POST",
              headers: { "Content-Type": "application/json" },
              body: JSON.stringify({ name: this.user, value: vote})
            };
            const response = await fetch('http://'+ this.ip +':8081/issue/' + this.issue + '/vote', requestOptions);
            const data = await response.json();
            // this.postId = data.id;
            this.responsesDemo.status = JSON.stringify(data);

            this.refresh()

        },

    async read() {    
      const resPhp = await fetch('http://'+ this.ip +':8081/static/getUsersForIssue');
      let res = await resPhp.json()
      this.responsesDemo.php = JSON.stringify(res);
    },

    async demoResponses() {
      const resPhp = await fetch('http://'+ this.ip +':8081/issue/' + this.issue);
      let res = await resPhp.json()
      // console.log(res)
      // this.members = (res.members.length == 0) ? [{name: '? (you)', vote: false}]:res.members
      this.members = res.members
      this.status = res.status
      this.responsesDemo.php = JSON.stringify(res);

      const resNode = await fetch('http://localhost:8082/issue/232');
      this.responsesDemo.node = JSON.stringify(await resNode.json());
    }
  }
}

</script>


<style scoped>

</style>
