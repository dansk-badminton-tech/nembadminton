import ApolloClient from 'apollo-boost'
import VueApollo from 'vue-apollo'

const apolloClient = new ApolloClient({
                                          uri: '/graphql'
                                      }
)

const apolloProvider = new VueApollo({
                                         defaultClient: apolloClient,
                                     }
)
export default apolloProvider
